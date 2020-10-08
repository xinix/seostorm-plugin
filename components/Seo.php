<?php

namespace Arcane\Seo\Components;

use Config;
use Cms\Components\ViewBag;
use Cms\Classes\ComponentBase;
use Arcane\Seo\Models\Settings;

class Seo extends ComponentBase
{
    public $settings;

    public $disable_schema;

    public $viewBag;

    public function componentDetails()
    {
        return [
            'name'        => 'SEO',
            'description' => 'Renders SEO meta tags in place'
        ];
    }

    public function defineProperties()
    {
        return [
            'disable_schema' => [
                'title' => 'Disable schemas',
                'description' => 'Enable this if you do not want to output schema scripts from the seo component.',
                'type' => 'checkbox'
            ]
        ];
    }

    public function onRun()
    {
        $this->settings = Settings::instance();

        if (!$this->page['viewBag']) {
            $this->page['viewBag'] = new ViewBag();
        }

        if ($this->page->page->hasComponent('blogPost')) {
            $post = $this->page['post'];
            $this->page['viewBag']->setProperties(array_merge(
                $this->page["viewBag"]->getProperties(),
                $post->attributes,
                $post->arcane_seo_options ?: []
            ));
        } elseif (isset($this->page->apiBag['staticPage'])) {
            $this->page['viewBag'] = $this->page->controller->vars['page']->viewBag;
        } else {
            $this->page['viewBag']->setProperties(array_merge($this->page['viewBag']->getProperties(), $this->page->settings));
        }
        $this->viewBag = $this->page['viewBag'];
        $this->disable_schema = $this->property('disable_schema');
        dd($this->page->layout->components);
    }

    public function getTitle()
    {
        $title = $this->page['title'];
        if (isset($this->viewBag['meta_title'])) {
            $title = $this->viewBag['meta_title'];
        }
        return $title;
    }

    public function getDescription()
    {
        $description = Settings::instance()->description;
        if (isset($this->page['description'])) {
            $description = $this->page['description'];
        }

        if (isset($this->viewBag['meta_description'])) {
            $description = $this->viewBag['meta_description'];
        }
        return $description;
    }

    public function getOgTitle()
    {
        $ogTitle = $this->getTitle();
        if (isset($this->viewBag['og_title'])) {
            $ogTitle = $this->viewBag['og_title'];
        }
        return $ogTitle;
    }

    public function getOgDescription()
    {
        $ogDescription = $this->getDescription();
        if (isset($this->viewBag['og_description'])) {
            $ogDescription = $this->viewBag['og_description'];
        }
        return $ogDescription;
    }

    public function getOgImage()
    {
        $mediaUrl = url(Config::get('cms.storage.media.path'));
        if ($settingsSiteImage = Settings::instance()->siteImage) {
            $ogImage = $mediaUrl . $settingsSiteImage;
        }

        if (isset($this->viewBag['og_image'])) {
            $ogImage = $mediaUrl . $this->viewBag['og_image'];
        }
        return $ogImage;
    }

    public function getOgVideo()
    {
        if (isset($this->viewBag['og_video'])) {
            $ogVideo = $this->viewBag['og_video'];
        }
        return $ogVideo;
    }

    public function getOgType()
    {
        $ogType = $this->viewBag['og_type'] ?? 'website';
        return $ogType;
    }
}
