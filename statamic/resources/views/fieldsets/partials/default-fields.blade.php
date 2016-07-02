<div class="form-group">
    <label>Field name</label>
    <input type="text" class="form-control"
           ng-model="fieldset.fields[selectedField].name"
           ng-blur="save()" />
    <small class="help-block">The field variable</small>
</div>

<div class="form-group">
    <label>Display text</label>
    <input type="text" class="form-control"
           ng-model="fieldset.fields[selectedField].display"
           ng-blur="save()" />
    <small class="help-block">The field's label</small>
</div>

<div class="form-group">
    <label>Required</label>
    <div class="checkbox">
        <label>
            <input type="checkbox"
                   ng-model="fieldset.fields[selectedField].required"
                   ng-change="save()" />
            This field is required
        </label>
    </div>
</div>

<div class="form-group">
    <label>Width</label>
    <select class="form-control"
            ng-model="fieldset.fields[selectedField].width"
            ng-change="save()"
            ng-integer>
        <option value="100">Full width</option>
        <option value="50">Half</option>
        <option value="25">1/4 - One quarter</option>
        <option value="75">3/4 - Three quarters</option>
        <option value="33">1/3 - One third</option>
        <option value="66">2/3 - Two thirds</option>
    </select>
    <small class="help-block">The size of the field in the fieldset layout.</small>
</div>

<div class="form-group">
    <label>Instructions</label>
    <textarea class="form-control" rows="2"
              ng-model="fieldset.fields[selectedField].instructions"
              ng-blur="save()"></textarea>
    <small class="help-block">Basic markdown is allowed</small>
</div>