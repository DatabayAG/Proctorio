# Proctorio

The key words "MUST", "MUST NOT", "REQUIRED", "SHALL", "SHALL NOT", "SHOULD",
"SHOULD NOT", "RECOMMENDED", "MAY", and "OPTIONAL"
in this document are to be interpreted as described in
[RFC 2119](https://www.ietf.org/rfc/rfc2119.txt).

**Table of Contents**

* [Requirements](#requirements)
* [Installation](#installation)
    * [Composer](#composer)
* [Know Issues](#known-issues)
* [License](#license)

## Requirements

* PHP: [![Minimum PHP Version](https://img.shields.io/badge/Minimum_PHP-7.2.x-blue.svg)](https://php.net/) [![Maximum PHP Version](https://img.shields.io/badge/Maximum_PHP-7.2.x-blue.svg)](https://php.net/)
* ILIAS: [![Minimum ILIAS Version](https://img.shields.io/badge/Minimum_ILIAS-5.4.0-orange.svg)](https://ilias.de/) [![Maximum ILIAS Version](https://img.shields.io/badge/Maximum_ILIAS-5.4.999-orange.svg)](https://ilias.de/)

## Installation

This plugin MUST be installed as a
[User Interface Plugin](https://www.ilias.de/docu/goto_docu_pg_39405_42.html).

The files MUST be saved in the following directory:

	<ILIAS>/Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/Proctorio

Correct file and folder permissions MUST be
ensured by the responsible system administrator.

### Composer

After the plugin files have been installed as described above,
please install the [`composer`](https://getcomposer.org/) dependencies:

```bash
cd Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/Proctorio
composer install --no-dev
```

### Known Issues

#### Same Site Cookie Policy

If the plugin is not working after the Proctorio pre-checks and the HTML
document does not show any progress, this might be caused by missing
cookies in the initial HTTP request when ILIAS is embedded in the Proctorio
document via an HTML `<iframe>`.

Please check your HTTP server (Nginx, Apache) logs and check if the ILIAS
cookies (primarily PHPSESSID and CLIENT_ID) are passed in the HTTP requests.
You should at least check all HTTP request where `TestLaunchAndReview.start`
is part of the HTTP request URL.

This is related to the [*SameSite*](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Set-Cookie/SameSite)
cookie policy of modern browsers.

As long as the ILIAS core does not support the configuration of this cookie
flag you'll have to patch the code fragments where ILIAS sets cookie parameters:

\ilInitialisation::setSessionCookieParams:
```php
// [...]
protected static function setSessionCookieParams()
{
    global $ilSetting;

    if (!defined('IL_COOKIE_SECURE')) {
        // If this code is executed, we can assume that \ilHTTPS::enableSecureCookies was NOT called before
        // \ilHTTPS::enableSecureCookies already executes session_set_cookie_params()

        include_once './Services/Http/classes/class.ilHTTPS.php';
        $cookie_secure = !$ilSetting->get('https', 0) && ilHTTPS::getInstance()->isDetected();
        define('IL_COOKIE_SECURE', $cookie_secure); // Default Value
        // proctorio-patch: begin
        $path = IL_COOKIE_PATH . '; samesite=None'; // With PHP >= 7.3 this could be done via the options array
        session_set_cookie_params(
            IL_COOKIE_EXPIRE,
            $path,
            IL_COOKIE_DOMAIN,
            IL_COOKIE_SECURE,
            IL_COOKIE_HTTPONLY
        );
        // proctorio-patch: end
    }
    // proctorio-patch: begin
    ilUtil::setCookie('ilClientId', CLIENT_ID);
    // proctorio-patch: end
}
// [...]
```

\ilUtil::setCookie:
```php
// [...]
// proctorio-patch: begin
$path = IL_COOKIE_PATH . '; samesite=None'; // With PHP >= 7.3 this could be done via the options array
setcookie(
    $a_cookie_name,
    $a_cookie_value,
    $expire,
    $path,
    IL_COOKIE_DOMAIN,
    $secure,
    IL_COOKIE_HTTPONLY
);
// proctorio-patch: end
// [...]
```

\ilAuthSession::init:
```php
// [...]
public function init()
{
    session_start();
    // proctorio-patch: begin
    ilUtil::setCookie(session_name(), session_id());
    // proctorio-patch: end
    $this->setId(session_id());
    
    $user_id = (int) ilSession::get(self::SESSION_AUTH_USER_ID);

    if ($user_id) {
        $this->getLogger()->debug('Resuming old session for user: ' . $user_id);
        $this->setUserId(ilSession::get(self::SESSION_AUTH_USER_ID));
        $this->expired = (int) ilSession::get(self::SESSION_AUTH_EXPIRED);
        $this->authenticated = (int) ilSession::get(self::SESSION_AUTH_AUTHENTICATED);
        
        $this->validateExpiration();
    } else {
        $this->getLogger()->debug('Started new session.');
        $this->setUserId(0);
        $this->expired = false;
        $this->authenticated = false;
    }
    return true;
}
// [...]
```

With PHP >= 7.3 the *SameSite* flag could be passed in the options array instead, see:
* https://www.php.net/manual/en/function.setcookie.php
* https://www.php.net/manual/en/function.session-set-cookie-params

## License

See LICENSE file in this repository.