<?php

/**
 * DeskPRO.
 */

namespace Application\AgentBundle;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * This generates JS hash router.
 */
class FragmentRouter
{
    /** @var array */
    protected $paths = [];
    /** @var array */
    protected $non_unique = [];

    /** @var \Symfony\Component\Routing\Generator\UrlGeneratorInterface */
    protected $generator;

    public function __construct(UrlGeneratorInterface $generator)
    {
        $this->generator = $generator;
    }

    public function compile($js_classname = null)
    {
        if (!$js_classname) {
            $js_classname = 'window.DeskPRO_FragmentRouter';
        }

        $js   = [];
        $js[] = "$js_classname = {\n\n";

        $js[] = "\tbaseUrl: '',\n\n";
        $js[] = "\tfragments: ".json_encode($this->generator->getFragmentInforArray()).",\n\n";

        $js[] = <<<EOF
    setBaseUrl: function (baseUrl) {
        this.baseUrl = baseUrl.replace(/\/$/, '');
    },

    hasFragment: function (fragment_name) {
        if (this.fragments[fragment_name] !== undefined) {
            return true;
        }

        return false;
    },

    getFragmentPattern: function (fragment_name) {
        if (!this.hasFragment(fragment_name)) return '';
        return this.fragments[fragment_name]['pattern'] || '';
    },

    getFragmentType: function (fragment_name) {
        if (!this.hasFragment(fragment_name)) return '';
        return this.fragments[fragment_name]['type'] || '';
    },

    getUrl: function (fragment_name, args) {
        var pattern = this.getFragmentPattern(fragment_name);

        var matches = pattern.match(/\{(.*?)\}/g);
        var m = null;
        var val = null;
        for (var i = 0; i < matches.length; i++) {
            m = matches[i];
            if (args[i] === undefined) {
                console.warn('Fragment %s was not provided with enough args: %o', fragment_name, args);
                break;
            }

            var val = args[i];
            if (typeof val == 'function') {
                val = val();
            }

            pattern = pattern.replace(m+'', args[i]);
        }

        return this.baseUrl + pattern;
    },

    getUrlNamedArgs: function (fragment_name, args) {
        var pattern = this.getFragmentPattern(fragment_name);

        Object.each(args, function (v,k) {
            if (typeof v == 'function') {
                v = v();
            }

            pattern = pattern.replace('{' + k + '}', v);
        });

        return this.baseUrl + pattern;
    }
}
EOF;

        return implode('', $js);
    }
}
