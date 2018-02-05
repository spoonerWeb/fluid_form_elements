# Fluid Form Elements

This is a TYPO3 extension which simplifies the usage of the Fluid form view helpers.

## Installation

`composer require spooner-web/fluid-form-elements`

## Why using these view helpers?

Are you tired of collecting the same records for your select again and again?<br>
Do you manage your labels for BE and FE twice?<br>
Do you copy/paste your template around the form elements?

Now these times are over. With Fluid Form Elements you:

* can set your own template wrapping the form elements
* can use the built-in Bootstrap templates (default and horizontal)
* get all records by the repository for your select automatically (based on the given property)
* get the label determined automatically on the general label `tablename.fieldname`
* can set the Bootstrap grid classes to use in horizontal form (default is col-sm-3/col-sm-9)

## Usage

After installation, the extension uses the namespace `ffe` globally.<br>
You are able to set the wanted template (currently default and horizontal Bootstrap form) in the extension configuration.<br>
Then you can start directly in your extension by using the new view helpers.

### Examples of usage

1. By using the textfield view helper `<ffe:form.textfield property="name" />` you get the Bootstrap template 
    ```
    div class="form-group">
        <label for="<!-- the property path, uses as ID -->"><!-- the determined label --></label>
        <!-- the rendered textfield by the original Fluid view helper -->
    </div>
    ```
    or horizontal Bootstrap (v4)
    ```
    <ffe:form classLabelColumn="col-sm-4" classFieldColumn="col-sm-8">
        <div class="form-group row">
            <label class="col-form-label <!-- given column bootstrap label class, set in <ffe:form />, default "col-sm-3" -->" for="<!-- the property path, uses as ID -->"><!-- the determined label --></label>
            <div class="<!-- given column bootstrap field class, set in <ffe:form />, default "col-sm-9" -->">
                <!-- the rendered textfield by the original Fluid view helper -->
            </div>
        </div>
    </ffe:form>
    ```
2. By using the select view helper `<ffe:form.select property="country" />` it determines what is given in your model behind the property `country`. If there is a relation to another model, it looks for the related repository and calls `findAll()` to get all records.
3. You can override arguments by using them, e.g. `<ffe:form.select property="country" objects="{countries}" />` and you can fill the select with your own data.
4. By using the argument `label` you can use your own label, e.g. `<ffe:form.textarea property="description" label="My description" />`

## Usable view helper

* FormViewHelper
* TextfieldViewHelper
* TextareaViewHelper
* SelectViewHelper
* CheckboxViewHelper
* RadioViewHelper

## Feedback

Please write me via [mail](mailto:loeffler@spooner-web.de) or create an issue in [GitLab](https://git.spooner.io/spooner/fluid_form_elements)

