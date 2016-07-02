<div class="panel panel-default">
    <div class="panel-body">

        <div ng-if="selectedField == null">
            <p>Choose a field to edit its settings.</p>
        </div>

        <div ng-if="selectedField !== null">
            @include('fieldsets.partials.default-fields')
            @include('fieldsets.partials.fieldtype-fields')
        </div>

    </div>
</div>