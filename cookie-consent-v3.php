<?php
namespace Grav\Plugin;

use Composer\Autoload\ClassLoader;
use Grav\Common\Plugin;

/**
 * Class CookieConsentV3Plugin
 * @package Grav\Plugin
 */
class CookieConsentV3Plugin extends Plugin
{
    /**
     * @return array
     *
     * The getSubscribedEvents() gives the core a list of events
     *     that the plugin wants to listen to. The key of each
     *     array section is the event that the plugin listens to
     *     and the value (in the form of an array) contains the
     *     callable (or function) as well as the priority. The
     *     higher the number the higher the priority.
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'onPluginsInitialized' => [
                // Uncomment following line when plugin requires Grav < 1.7
                // ['autoload', 100000],
                ['onPluginsInitialized', 0]
            ]
        ];
    }

    /**
     * Composer autoload
     *
     * @return ClassLoader
     */
    public function autoload(): ClassLoader
    {
        return require __DIR__ . '/vendor/autoload.php';
    }

    /**
     * Initialize the plugin
     */
    public function onPluginsInitialized(): void
    {
        if ($this->isAdmin()) {
            return;
        }

        $this->enable([
            'onTwigTemplatePaths' => ['onTwigTemplatePaths', 0],
            'onTwigSiteVariables' => ['onTwigSiteVariables', 0]
        ]);
    }

    public function onTwigTemplatePaths()
    {
        $this->grav['twig']->twig_paths[] = __DIR__ . '/templates';
    }

    public function onTwigSiteVariables()
    {
        $twig = $this->grav['twig'];

        $cdnEnabled = $this->config->get('plugins.cookie-consent-v3.cdn.enabled');
        $useCustomTexts = $this->config->get('plugins.cookie-consent-v3.overwrite_languages');

        if ($cdnEnabled) {
            $this->grav['assets']->addCss("//cdn.jsdelivr.net/gh/orestbida/cookieconsent@3.0.1/dist/cookieconsent.css");
            $this->grav['assets']->addJsModule("//cdn.jsdelivr.net/gh/orestbida/cookieconsent@3.0.1/dist/cookieconsent.umd.js");
        }
        else {
            $this->grav['assets']->addCss("plugin://cookie-consent-v3/assets/cookieconsent.min.css");
            $this->grav['assets']->addJsModule("plugin://cookie-consent-v3/assets/cookieconsent.umd.js");
        }

        /**
         * add CookieConsent v3 lib
         */
        $this->grav['assets']->addInlineJsModule($twig->twig->render('partials/umd.js.twig', [
            'content' => $useCustomTexts?$this->config->get('plugins.cookie-consent-v3.content'):[],
            'links' => $this->config->get('plugins.cookie-consent-v3.links'),
            'theme' => $this->config->get('plugins.cookie-consent-v3.theme')
        ]));
    }
}
