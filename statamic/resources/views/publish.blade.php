@extends('layout')

@section('content')

    <publish title="{{ $title }}"
             extra="{{ json_encode($extra) }}"
             :is-new="{{ bool_str($is_new) }}"
             content-type="{{ $content_type }}"
             uuid="{{ $uuid }}"
             content-data="{{ json_encode($content_data) }}"
             fieldset-name="{{ $fieldset }}"
             slug="{{ $slug }}"
             url="{{ $url }}"
             :status="{{ bool_str($status) }}"
             locale="{{ $locale }}"
             locales="{{ json_encode($locales) }}"
             :is-default-locale="{{ bool_str($is_default_locale) }}"
             :remove-title="true"
    ></publish>

@endsection
