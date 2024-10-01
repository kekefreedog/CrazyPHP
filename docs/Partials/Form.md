# Form - Partial

## About

Partial Form allow to add a form into your page of any component that is calling partial assets.

- Handlebar template can be found here : `/assets/Hbs/partials/form.hbs`
- Typscript script can be found here : `/app/Environment/Partials/Form.ts`

## Schema

```yml
# Simple form
-
    id: "form_id"               # Id of the form
    title: Form                 # Name / Label of the form
    entity: null                # Main entity on the database
    onready: null
    reset: true
    items:
        -   # Exemple of simple text input
            name: text_input
            type: text
            label: Text Input
        -   # Exemple of simple checkbox
            name: checkbox_input
            type: checkbox
            label: Checkbox Input
        -   # Exemple of simple switch
            name: radio_input
            type: radio
            label: Radio Input
            select: 
                -
                    label: Option 1
                    value: 1
                -
                    label: Option 2
                    value: 2
                -
                    label: Option 3
                    value: 3
        -   # Simple switch
            name: switch_input
            type: switch
            label: Switch Input
        -   # Simple range
            name: range_input
            type: range
            label: Range Input
```

```yml
# Disabled form
-
    id: "form_id"               # Id of the form
    title: Basic Form           # Name / Label of the form
    entity: null                # Main entity on the database
    onready: null
    reset: true
    items:
        -   # Exemple of disabled text input
            name: disabled_text_input
            type: text
            label: Disabled Text Input
            disabled: true
        -   # Exemple of simple checkbox
            name: disabled_checkbox_input
            type: checkbox
            label: Disabled Checkbox Input
            disabled: true
        -   # Exemple of simple switch
            name: disabled_radio_input
            type: radio
            label: Disabled Radio Input
            disabled: true
            select: 
                -
                    label: Option 1
                    value: 1
                -
                    label: Option 2
                    value: 2
                -
                    label: Option 3
                    value: 3
        -   # Exemple of partial simple switch
            name: partial_disabled_radio_input
            type: radio
            label: Partial Disabled Radio Input
            select: 
                -
                    label: Option 1
                    value: 1
                    disabled: true
                -
                    label: Option 2
                    value: 2
                -
                    label: Option 3
                    value: 3
                    disabled: true
        -   # Disabled Simple switch
            name: disabled_switch_input
            type: switch
            label: Disabled Switch Input
            disabled: true
        -   # Simple range
            name: disabled_range_input
            type: range
            label: Disabled Range Input
            disabled: true
```

```yml
# Readonly form
-
    id: "form_id"               # Id of the form
    title: Basic Form           # Name / Label of the form
    entity: null                # Main entity on the database
    onready: null
    reset: true
    items:
        -   # Exemple of disabled text input
            name: readonly_text_input
            type: text
            label: Read Only Text Input
            readonly: true
```


```yml
# Custom form
-
    id: "form_id"               # Id of the form
    title: Form                 # Name / Label of the form
    entity: null                # Main entity on the database
    onready: null
    reset: true
    items:
        -   # Exemple of simple text input with placeholder
            name: placeholder_text_input
            type: text
            label: Placeholder Text Input
            placeholder: Text used as placeholder
        -   # Exemple of simple text input with custom class
            name: placeholder_text_input
            type: text
            label: Placeholder Text Input
            _style:
                prefix:
                    class: material-icons
                    text: place
                suffix:
                    class: material-icons
                    text: gps_fixed
                customClass: 
                    input-field: outlined
        -   # Exemple of checkbox with custom class
            name: checkbox_input
            type: checkbox
            label: Checkbox Input
            _style:
                customClass: 
                    input: filled-in
        -   # Exemple of range with custum min and max
            name: custom_range_input_min_max
            type: range
            label: Custom Range Min and Max Input
            select:
                -   # Min
                    value: 10
                -   # Max
                    value: 90
        -   # Exemple of range with custum step
            name: custom_range_input_step
            type: range
            label: Custom Range with Step Input
            _style:
                range:
                    # Step
                    step: 10
        -   # Exemple of how get current date in default field
            name: today_date_input
            type: date
            label: Current Date Input
            default: today() # Or yesterday() or tomorrow()
        -   # Custom number min and max
            name: custom_number_input_min_max
            type: number
            label: Custom Number Min and Max Input
            select:
                - 
                    value: 10
                -
                    value: 90
        -   # Text input with min and max
            name: text_input_min_max
            type: text
            label: Text Input With Min And Max
            _style:
                text:
                    minlength: 2
                    maxlength: 10
```