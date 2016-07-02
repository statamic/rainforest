<?php

namespace Statamic\Extend;

use Statamic\API\Arr;
use Statamic\API\Parse;
use Statamic\Data\DataCollection;

/**
 * Template tags
 */
abstract class Tags extends Addon
{
    use HasParameters;

    /**
     * The content written between the tags (when a tag pair)
     * @public string
     */
    public $content;

    /**
     * The variable context around which this tag is positioned
     * @public array
     */
    public $context;

    /**
     * The tag that was used (without any parameters), eg. ron:swanson
     * @var string
     */
    public $tag;

    /**
     * If is a tag pair
     * @var bool
     */
    public $isPair;

    /**
     * Whether to trim the whitespace from the content before parsing
     * @var  bool
     */
    protected $trim = false;

    /**
     * Set the properties
     *
     * @param array  $properties  Properties that to set
     * @return Tags
     */
    public function __construct($properties)
    {
        $this->parameters  = $properties['parameters'];
        $this->content     = $properties['content'];
        $this->context     = $properties['context'];
        $this->isPair      = $this->content !== '';
        $this->tag         = array_get($properties, 'tag');

        parent::__construct();
    }

    /**
     * Trim the content
     *
     * @param   bool    $trim  Whether to trim the content
     * @return  this
     */
    protected function trim($trim = true)
    {
        $this->trim = $trim;

        return $this;
    }

    /**
     * Parse the tag pair contents with scoped variables
     *
     * @param array $data     Data to be parsed into template
     * @param array $context  Contextual variables to also use
     * @return string
     */
    protected function parse($data, $context = [])
    {
        if ($this->trim) {
            $this->content = trim($this->content);
        }

        $context = array_merge($context, $this->context);

        return Parse::template($this->content, $this->addScope($data), $context);
    }

    /**
     * Iterate over the data and parse the tag pair contents for each, with scoped variables
     *
     * @param array|\Statamic\Data\DataCollection $data        Data to iterate over
     * @param bool                                $supplement  Whether to supplement with contextual values
     * @param array                               $context     Contextual variables to also use
     * @return string
     */
    protected function parseLoop($data, $supplement = true, $context = [])
    {
        if ($this->trim) {
            $this->content = trim($this->content);
        }

        $context = array_merge($context, $this->context);

        return Parse::templateLoop($this->content, $this->addScope($data), $supplement, $context);
    }

    /**
     * Add the provided $data to its own scope
     *
     * @param array|\Statamic\Data\DataCollection $data
     * @return mixed
     */
    private function addScope($data)
    {
        if ($scope = $this->getParam('scope')) {
            $data = Arr::addScope($data, $scope);
        }

        if ($data instanceof DataCollection) {
            $data = $data->toArray();
        }

        return $data;
    }

    /**
     * Open a form tag
     *
     * @param  string $action
     * @return string
     */
    protected function formOpen($action)
    {
        $attr_str = '';
        if ($attrs = $this->getList('attr')) {
            foreach ($attrs as $attr) {
                list($param, $value) = explode(':', $attr);
                $attr_str .= $param . '="' . $value . '" ';
            }
        }

        if ($this->getBool('files')) {
            $attr_str .= 'enctype="multipart/form-data"';
        }

        $action = $this->eventUrl($action);

        $html = '<form method="POST" action="'.$action.'" '.$attr_str.'>';

        return $html;
    }
}
