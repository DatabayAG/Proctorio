<?php declare(strict_types=1);

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/

namespace ILIAS\Plugin\Proctorio\Webservice\Rest;

use GuzzleHttp\Exception\GuzzleException;
use ILIAS\Data\URI;
use ILIAS\Plugin\Proctorio\Administration\GeneralSettings\Settings;
use ILIAS\Plugin\Proctorio\Data\TrustedURI;
use ILIAS\Plugin\Proctorio\Refinery\Transformation\UriToString;
use ILIAS\Plugin\Proctorio\Service\Proctorio\Impl as ProctorioService;
use ILIAS\Plugin\Proctorio\Webservice\Exception;
use ILIAS\Plugin\Proctorio\Webservice\Exception\QualifiedResponseError;
use ILIAS\Plugin\Proctorio\Webservice\Rest;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use ilLogger;
use ilObjTest;
use ilObjTestGUI;
use ilTestEvaluationGUI;
use ilTestPasswordProtectionGUI;
use ilTestPlayerCommands;
use ilTestPlayerFixedQuestionSetGUI;
use ilTestPlayerRandomQuestionSetGUI;
use ilTestSubmissionReviewGUI;

/**
 * Class Impl
 * @package ILIAS\Plugin\Proctorio\Webservice\Rest
 * @author Michael Jansen <mjansen@databay.de>
 */
class Impl implements Rest
{
    /** @var ProctorioService */
    private $service;
    /** @var ilLogger */
    private $logger;
    /** @var Settings */
    private $proctorioSettings;

    public function __construct(ProctorioService $service, Settings $proctorioSettings, ilLogger $logger)
    {
        $this->service = $service;
        $this->proctorioSettings = $proctorioSettings;
        $this->logger = $logger;
    }

    /**
     * @param ilObjTest $test
     * @param URI $testLaunchUrl
     * @param URI $testUrl
     * @return string
     * @throws GuzzleException
     */
    private function request(
        ilObjTest $test,
        URI $testLaunchUrl,
        URI $testUrl
    ) : string {
        $this->logger->debug('Executing Proctorio API call');

        $baseUrlWithScript = $testUrl->getSchema() . '://' . $testUrl->getHost();
        $regexQuotedBaseUrlWithScript = preg_quote($baseUrlWithScript, '/');

        $startRegexWithBaseUrl = $this->buildExamStartRegex($test, $regexQuotedBaseUrlWithScript, $testLaunchUrl);
        $takeRegex = $this->buildExamTakeRegex($test);
        $endRegex = $this->buildExamEndRegex($test);

        $this->logger->debug(sprintf(
            "Regular Expressions: Start Exam: %s / Take Exam: %s / End Exam: %s",
            $startRegexWithBaseUrl,
            $regexQuotedBaseUrlWithScript . $takeRegex,
            $regexQuotedBaseUrlWithScript . $endRegex
        ));
        $this->logger->debug(sprintf(
            "Parameter lengths: Start Exam: %s / Take Exam: %s / End Exam: %s",
            strlen($startRegexWithBaseUrl),
            strlen($regexQuotedBaseUrlWithScript . $takeRegex),
            strlen($regexQuotedBaseUrlWithScript . $endRegex)
        ));

        $testLaunchUrlString = (new UriToString())->transform($testLaunchUrl);
        $testLaunchUrlString .= ('#' . $test->getId());

        $this->logger->info(sprintf(
            "Effective Exam Settings: %s",
            implode(',', $this->service->getConfigurationForTest($test)['exam_settings'])
        ));
        $this->logger->info(sprintf("Launch URL: %s", $testLaunchUrlString));

        $postParameters = [
            'launch_url' => $testLaunchUrlString,
            'user_id' => (string) $this->service->getActor()->getId(),
            'oauth_consumer_key' => $this->proctorioSettings->getApiKey(),
            'exam_start' => $startRegexWithBaseUrl,
            'exam_take' => $regexQuotedBaseUrlWithScript . $takeRegex,
            'exam_end' => $regexQuotedBaseUrlWithScript . $endRegex . '|(' . '^((?!ref_id=' . $test->getRefId() . ').)*$' . ')',
            'exam_settings' => implode(',', $this->service->getConfigurationForTest($test)['exam_settings']),
            'fullname' => $this->service->getActor()->getFullname(),
            'exam_tag' => (string) $test->getId(),
            'oauth_signature_method' => 'HMAC-SHA1',
            'oauth_version' => '1.0',
            'oauth_timestamp' => (string) time(),
            'oauth_nonce' => md5(uniqid((string) mt_rand(), true)),
        ];
        $consumerSecret = $this->proctorioSettings->getApiSecret();

        $region = $this->proctorioSettings->getApiRegion();
        $url = str_replace('[ACCOUNT_REGION]', $region, $this->proctorioSettings->getApiBaseUrl());
        $urlPath = ltrim($this->proctorioSettings->getApiLaunchAndReviewEndpoint(), '/');

        $this->logger->debug('HTTP POST Parameters: ' . print_r($postParameters, true));

        // https://csharp.hotexamples.com/de/site/file?hash=0xee75c4aef1fe45609c1c1cfc2677509faae0583c603d230fce0c1559b16dddb8&fullName=LtiLibrary.Core/OAuthUtility.cs&project=andyfmiller/LtiLibrary
        // https://github.com/andyfmiller/LtiLibrary/blob/master/src/LtiLibrary.NetCore/Extensions/NameValueCollectionExtensions.cs#L20
        // https://github.com/andyfmiller/LtiLibrary/blob/master/src/LtiLibrary.NetCore/Extensions/StringExtensions.cs
        $signatureData = '';
        foreach ($postParameters as $key => $value) {
            $signatureData .= '&' . rawurlencode($key) . '=' . rawurlencode($value);
        }
        $signatureData = ltrim($signatureData, '&');

        $signatureBaseString = 'POST&' . rawurlencode($url . '/' . $urlPath) . '&' . rawurlencode($signatureData);

        $this->logger->debug('Signature Base String: ' . $signatureBaseString);
        $signature = hash_hmac('sha1', $signatureBaseString, $consumerSecret, true);
        $this->logger->debug('Signature: ' . $signature);
        $base64EncodedData = base64_encode($signature);
        $this->logger->debug('OAuth Signature: ' . $base64EncodedData);

        $postParameters['oauth_signature'] = $base64EncodedData;

        $container = [];
        $history = Middleware::history($container);
        $stack = HandlerStack::create();
        $stack->push($history);

        $client = new Client([
            'handler' => $stack,
            'base_uri' => $url
        ]);

        $formParams = [];
        foreach ($postParameters as $key => $value) {
            $formParams[$key] = $value;
        }

        $response = $client->request('POST', $urlPath, [
            'form_params' => $formParams
        ]);

        foreach ($container as $transaction) {
            $httpHeaderArray = $transaction['request']->getHeaders();
            $requestBody = (string) $transaction['request']->getBody();

            $this->logger->debug('HTTP Request Headers: ' . print_r($httpHeaderArray, true));
            $this->logger->debug('HTTP Request Body: ' . $requestBody);
        }

        $body = $response->getBody();

        $this->logger->debug('HTTP Response Status: ' . $response->getStatusCode() . ' ' . $response->getReasonPhrase());
        $this->logger->debug('HTTP Response Body: ' . (string) $body);

        return (string) $body;
    }

    /**
     * @param string $responseBody
     * @throws Exception
     * @throws QualifiedResponseError
     */
    private function handleResponseException(string $responseBody) : void
    {
        if (is_numeric($responseBody)) {
            throw new QualifiedResponseError(sprintf(
                "Unexpected Proctorio API Response: %s",
                $responseBody
            ), (int) $responseBody);
        }

        throw new Exception(sprintf(
            "Unexpected Proctorio API Response: %s",
            $responseBody
        ));
    }

    /**
     * @inheritDoc
     * @throws Exception
     * @throws QualifiedResponseError
     * @throws GuzzleException
     */
    public function getLaunchUrl(
        ilObjTest $test,
        URI $testLaunchUrl,
        URI $testUrl
    ) : URI {
        $responseBody = $this->request($test, $testLaunchUrl, $testUrl);

        if ($responseBody !== '') {
            $responseArray = json_decode($responseBody, true, 512, JSON_THROW_ON_ERROR);
            $isLaunchApiSuccess = is_array($responseArray) && isset($responseArray[0]) && is_string($responseArray[0]) && $responseArray[0] !== '';
            if ($isLaunchApiSuccess) {
                return new TrustedURI($responseArray[0]);
            }
        }

        $this->handleResponseException($responseBody);
    }

    /**
     * @inheritDoc
     * @throws Exception
     * @throws QualifiedResponseError
     * @throws GuzzleException
     */
    public function getReviewUrl(
        ilObjTest $test,
        URI $testLaunchUrl,
        URI $testUrl
    ) : URI {
        $responseBody = $this->request($test, $testLaunchUrl, $testUrl);

        if ($responseBody !== '') {
            $responseArray = json_decode($responseBody, true, 512, JSON_THROW_ON_ERROR);
            $isReviewApiSuccess = is_array($responseArray) && isset($responseArray[1]) && is_string($responseArray[1]) && $responseArray[1] !== '';
            if ($isReviewApiSuccess) {
                return new TrustedURI($responseArray[1]);
            }
        }

        $this->handleResponseException($responseBody);
    }

    /**
     * @param ilObjTest $test
     * @param string $regexQuotedBaseUrlWithScript
     * @param URI $testLaunchUrl
     * @return string
     */
    private function buildExamStartRegex(
        ilObjTest $test,
        string $regexQuotedBaseUrlWithScript,
        URI $testLaunchUrl
    ) : string {
        $startParameterNames = ['ref_id', 'cmd'];
        $startParameterValues = [$test->getRefId(), 'TestLaunchAndReview\.start'];
        $startRegex = '((.*?([\?&]';
        $startRegex .= '(' . implode('|', $startParameterNames) . ')=(' . implode('|', $startParameterValues) . ')';
        $startRegex .= ')){2})';

        return $regexQuotedBaseUrlWithScript . $startRegex;
    }

    /**
     * @param ilObjTest $test
     * @return string
     */
    private function buildExamTakeRegex(ilObjTest $test) : string
    {
        $parameterValues = [
            ilTestPlayerCommands::START_TEST,
            ilTestPlayerCommands::INIT_TEST,
            ilTestPlayerCommands::START_PLAYER,
            ilTestPlayerCommands::RESUME_PLAYER,
            //\ilTestPlayerCommands::DISPLAY_ACCESS_CODE,
            //\ilTestPlayerCommands::ACCESS_CODE_CONFIRMED,
            ilTestPlayerCommands::SHOW_QUESTION,
            ilTestPlayerCommands::PREVIOUS_QUESTION,
            ilTestPlayerCommands::NEXT_QUESTION,
            ilTestPlayerCommands::EDIT_SOLUTION,
            //\ilTestPlayerCommands::MARK_QUESTION,
            //\ilTestPlayerCommands::MARK_QUESTION_SAVE,
            //\ilTestPlayerCommands::UNMARK_QUESTION,
            //\ilTestPlayerCommands::UNMARK_QUESTION_SAVE,
            ilTestPlayerCommands::SUBMIT_INTERMEDIATE_SOLUTION,
            ilTestPlayerCommands::SUBMIT_SOLUTION,
            ilTestPlayerCommands::SUBMIT_SOLUTION_AND_NEXT,
            ilTestPlayerCommands::REVERT_CHANGES,
            //\ilTestPlayerCommands::DETECT_CHANGES,
            //\ilTestPlayerCommands::DISCARD_SOLUTION,
            ilTestPlayerCommands::SKIP_QUESTION,
            ilTestPlayerCommands::SHOW_INSTANT_RESPONSE,
            //\ilTestPlayerCommands::CONFIRM_HINT_REQUEST,
            //\ilTestPlayerCommands::SHOW_REQUESTED_HINTS_LIST,
            ilTestPlayerCommands::QUESTION_SUMMARY,
            //\ilTestPlayerCommands::QUESTION_SUMMARY_INC_OBLIGATIONS,
            //\ilTestPlayerCommands::QUESTION_SUMMARY_OBLIGATIONS_ONLY,
            ilTestPlayerCommands::TOGGLE_SIDE_LIST,
            ilTestPlayerCommands::SHOW_QUESTION_SELECTION,
            //\ilTestPlayerCommands::UNFREEZE_ANSWERS,
            //\ilTestPlayerCommands::AUTO_SAVE,
            //\ilTestPlayerCommands::REDIRECT_ON_TIME_LIMIT,
            ilTestPlayerCommands::SUSPEND_TEST,
            ilTestPlayerCommands::FINISH_TEST,
            ilTestPlayerCommands::AFTER_TEST_PASS_FINISHED,
            ilTestPlayerCommands::BACK_TO_INFO_SCREEN,
            ilTestPlayerCommands::BACK_FROM_FINISHING,
            'show',
            $test->getRefId(),
        ];

        // Because Proctorio does not support long regular expressions, we have to use a short/weak one
        $parameterValues = [
            $test->getRefId(),
        ];

        $parameterValues[] = strtolower(ilTestSubmissionReviewGUI::class);
        $parameterValues[] = strtolower(ilTestPasswordProtectionGUI::class);
        if ($test->isRandomTest()) {
            $parameterValues[] = strtolower(ilTestPlayerRandomQuestionSetGUI::class);
        } elseif ($test->isFixedTest()) {
            $parameterValues[] = strtolower(ilTestPlayerFixedQuestionSetGUI::class);
        }

        $parameterNames = [
            //'cmd',
            //'fallbackCmd',
            'ref_id',
            'cmdClass',
        ];

        $this->logger->info("Initiating Proctorio API call ...");

        $takeRegex = '(.*?([\?&]';
        $takeRegex .= '(' . implode('|', $parameterNames) . ')=(' . implode('|', $parameterValues) . ')';
        $takeRegex .= ')){2}';// 3

        return $takeRegex;
    }

    /**
     * @param ilObjTest $test
     * @return string
     */
    private function buildExamEndRegex(ilObjTest $test) : string
    {
        $evaluationParameterNames = ['ref_id', 'cmdClass'];
        $evaluationParameterValues = [$test->getRefId(), strtolower(ilTestEvaluationGUI::class)];
        $endRegexEval = '((.*?([\?&]';
        $endRegexEval .= '(' . implode('|', $evaluationParameterNames) . ')=(' . implode(
            '|',
            $evaluationParameterValues
        ) . ')';
        $endRegexEval .= ')){2})';

        $infoParameterNames = ['ref_id', 'cmdClass', 'cmd'];
        $infoParameterValues = [$test->getRefId(), strtolower(ilObjTestGUI::class), 'redirectToInfoScreen'];
        $endRegexInfo = '((.*?([\?&]';
        $endRegexInfo .= '(' . implode('|', $infoParameterNames) . ')=(' . implode('|', $infoParameterValues) . ')';
        $endRegexInfo .= ')){3})';

        $endRegexParts = [
            $endRegexEval,
            $endRegexInfo,
        ];

        $this->appendFinalStatementUrlToExamEndRegex($test, $endRegexParts);
        $this->appendRedirectUrlToExamEndRegex($test, $endRegexParts);

        $endRegex = implode('|', $endRegexParts);

        return $endRegex;
    }

    /**
     * @param ilObjTest $test
     * @param string[] $endRegexParts
     */
    private function appendRedirectUrlToExamEndRegex(ilObjTest $test, array &$endRegexParts) : void
    {
        $redirectMode = (int) $test->getRedirectionMode();
        $redirectUrl = $test->getRedirectionUrl();

        if (
            is_string($redirectUrl) &&
            $redirectUrl !== ''
            && in_array($redirectMode, [REDIRECT_ALWAYS, REDIRECT_KIOSK], true)
        ) {
            $doAppend = false;

            if ($redirectMode === REDIRECT_KIOSK) {
                if ($test->getKioskMode()) {
                    $doAppend = true;
                }
            } else {
                $doAppend = true;
            }

            if ($doAppend) {
                $urlParts = parse_url($redirectUrl);

                if (is_string($urlParts['query']) && $urlParts['query'] !== '') {
                    $endRegexParts[] = '(.*?' . preg_quote($urlParts['query'], '/') . ')';
                } elseif (is_string($urlParts['path']) && $urlParts['path'] !== '') {
                    $endRegexParts[] = '(.*?' . preg_quote($urlParts['path'], '/') . ')';
                } else {
                    $endRegexParts[] = '(.*?' . preg_quote($urlParts['host'], '/') . ')';
                }
            }
        }
    }

    /**
     * @param ilObjTest $test
     * @param string[] $endRegexParts
     */
    private function appendFinalStatementUrlToExamEndRegex(ilObjTest $test, array &$endRegexParts) : void
    {
        if ($test->getShowFinalStatement()) {
            $finalStatementAndItemIntroParameterNames = ['cmd'];
            $finalStatementAndItemIntroValues = [ilTestPlayerCommands::SHOW_FINAL_STATMENT];
            $endRegexFinalStatementAndItemIntro = '((.*?([\?&]';
            $endRegexFinalStatementAndItemIntro .= '(' . implode(
                '|',
                $finalStatementAndItemIntroParameterNames
            ) . ')=(' . implode(
                '|',
                $finalStatementAndItemIntroValues
            ) . ')';
            $endRegexFinalStatementAndItemIntro .= ')){1})';

            $endRegexParts[] = $endRegexFinalStatementAndItemIntro;
        }
    }
}
