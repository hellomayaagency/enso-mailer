<?php

namespace Hellomayaagency\Enso\Mailer\Crud;

use Hellomayaagency\Enso\Mailer\Contracts\Audience as AudienceContract;
use Illuminate\Support\Facades\App;
use Yadda\Enso\Crud\Config;
use Yadda\Enso\Crud\Forms\Form;
use Yadda\Enso\Crud\Tables\Text;

class Audience extends Config
{
    public function configure()
    {
        $this->setModel(get_class(App::make(AudienceContract::class)))
            ->setRoute('admin.mailer.audiences')
            ->setViewsDir('mailer_audiences')
            ->setNameSingular('Audience')
            ->setNamePlural('Audiences')
            ->setPaginate(true)
            ->setPerPage(15)
            ->setPreloadRelationships([
                'conditions',
            ])
            ->setTableColumns([
                (new Text('name')),
                (new Text('user_count')),
            ])
            ->setRules([
                'name' => ['required', 'string', 'max:255'],
                'conditions' => ['mailer_conditions'],
            ]);

        $this->addIndexAction('show', [
            'component' => 'link-button',
            'route' => '/%ID%/users',
            'title' => 'User list',
            'wrapperClass' => 'button',
            'button' => [
                'type' => 'fa',
                'content' => 'fa fa-users',
            ],
            'order' => 9,
        ]);
    }

    /**
     * Gets the View template name to render for a specific view type
     *
     * This should be overridden in cases where you have non-standard
     * templates to render as partack  of your config that should stay as
     * part of enso and not be directly published
     *
     * @param string $view_name
     *
     * @return string
     */
    protected function getEnsoView($view_name)
    {
        $view_folder = $this->getViewsDir();

        $views = [
            'enso-crud::' . $view_folder . '.' . $view_name,
        ];

        foreach ($views as $view) {
            if (view()->exists($view)) {
                return $view;
            }
        }

        return 'enso-crud::' . $view_name;
    }

    public function create(Form $form)
    {
        return $form;
    }
}
