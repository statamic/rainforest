<?php

namespace Statamic\Addons\Crud;

use Statamic\API\Crypt;
use Statamic\Extend\Tags;

class CrudTags extends Tags
{
    protected $type;

    public function __call($method, $args)
    {
        return array_get($this->context, $this->tag);
    }

    public function form()
    {
        $this->type = explode(':', $this->tag)[0];

        $action = $this->eventUrl('post/' . $this->type);

        $output = '<form method="post" action="'.$action.'">';
        $output .= '<input type="text" name="params" value="'.$this->getParams().'" />';
        $output .= $this->content;
        $output .= '</form>';

        return $output;
    }

    private function getParams()
    {
        $params = [
            'published' => $this->getBool('published')
        ];

        if ($this->type == 'entry') {
            $params['collection'] = $this->get('collection');
        }

        return Crypt::encrypt($params);
    }
}
