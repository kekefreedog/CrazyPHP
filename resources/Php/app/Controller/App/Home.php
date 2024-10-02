<?php declare(strict_types=1);
/**
 * App
 *
 * Workflow of your app
 *
 * PHP version 8.1.2
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
namespace App\Controller\App;

/**
 * Dependances
 */
use CrazyPHP\Library\Html\Structure;
use CrazyPHP\Core\Controller;
use CrazyPHP\Core\Response;


 /**
 * App
 *
 * Main methods of you apps
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2024 Kévin Zarshenas
 */
class Home extends Controller {

    /** @const string TEMPLATE */
    public const TEMPLATE = "@app_root/app/Environment/Page/Home/template.hbs";

    /**
     * Get
     */
    public static function get($request){
    
        # Render state
        $state = static::State()
            ->pushColorSchema()
            ->pushForm(static::SIMPLE_FORM)
            ->pushForm(static::DISABLED_FORM)
            ->pushForm(static::CUSTOM_FORM)
            ->pushForm(static::READONLY_FORM)
            ->pushForm(static::DEFAULT_FORM)
            ->pushForm(static::REQUIRED_FORM)
            ->render()
        ;

        # Set structure
        $structure = (new Structure())
            ->setDoctype()
            ->setLanguage()
            ->setHead()
            ->setJsScripts()
            ->setBodyTemplate(self::TEMPLATE, null, (array) $state)
            ->prepare()
            ->render()
        ;

        # Set response
        (new Response())
            ->setContent($structure)
            ->send();

    }
    /** Public constant
     ******************************************************
     */

    /** @var array FORM_SIMPLE */
    public const SIMPLE_FORM = [
        "id"            =>  "simple_form",
        "title"         =>  "Simple Form",
        "entity"        =>  null,
        "onready"       =>  null,
        "reset"         =>  true,
        "items"         =>  [
            # Simple text input
            [
                "name"      =>  "text_input",
                "type"      =>  "text",
                "label"     =>  "Text Input"
            ],
            # Simple email input
            [
                "name"      =>  "email_input",
                "type"      =>  "email",
                "label"     =>  "Email Input",
            ],
            # Simple checkbox
            [
                "name"      =>  "checkbox_input",
                "type"      =>  "checkbox",
                "label"     =>  "Checkbox Input",
            ],
            # Simple radio
            [
                "name"      =>  "radio_input",
                "type"      =>  "radio",
                "label"     =>  "Radio Input",
                "select"    =>  [
                    [
                        "label" =>  "Option 1",
                        "value" =>  1
                    ],
                    [
                        "label" =>  "Option 2",
                        "value" =>  2
                    ],
                    [
                        "label" =>  "Option 3",
                        "value" =>  3
                    ],
                ]
            ],
            # Simple switch
            [
                "name"      =>  "switch_input",
                "type"      =>  "switch",
                "label"     =>  "Switch Input",
            ],
            # Simple range
            [
                "name"      =>  "range_input",
                "type"      =>  "range",
                "label"     =>  "Range Input",
            ],
            # Simple number
            [
                "name"      =>  "number_input",
                "type"      =>  "number",
                "label"     =>  "Number Input",
            ],
            # Date number
            [
                "name"      =>  "date_input",
                "type"      =>  "date",
                "label"     =>  "Date Input",
            ],
            # Password
            [
                "name"      =>  "password_input",
                "type"      =>  "password",
                "label"     =>  "Password Input",
            ],
        ]
    ];

    /** @var array FORM_SIMPLE */
    public const CUSTOM_FORM = [
        "id"            =>  "custom_form",
        "title"         =>  "Custom Form",
        "entity"        =>  null,
        "onready"       =>  null,
        "reset"         =>  true,
        "items"         =>  [
            # Placeholder text input with placeholder
            [
                "name"          =>  "placeholder_text_input",
                "type"          =>  "text",
                "label"         =>  "Placeholder Text Input",
                "placeholder"   =>  "Text used as placeholder"
            ],
            # Placeholder text input with custom class
            [
                "name"          =>  "custom_class_text_input",
                "type"          =>  "text",
                "label"         =>  "Text Input With Custom Class",
                "_style"        =>  [
                    "customClass"   =>  [
                        "input-field"   =>  "outlined"
                    ]
                ]
            ],
            # Text input with prefix
            [
                "name"          =>  "prefix_text_input",
                "type"          =>  "text",
                "label"         =>  "Text Input With Prefix",
                "_style"        =>  [
                    "prefix"        =>  [
                        "class"         =>  "material-icons",
                        "text"          =>  "place",
                    ]   
                ]
            ],
            # Text input with suffix
            [
                "name"          =>  "prefix_text_input",
                "type"          =>  "text",
                "label"         =>  "Text Input With Prefix",
                "_style"        =>  [
                    "suffix"        =>  [
                        "class"         =>  "material-icons",
                        "text"          =>  "place",
                    ]   
                ]
            ],
            # Simple checkbox with custom class
            [
                "name"      =>  "checkbox_input",
                "type"      =>  "checkbox",
                "label"     =>  "Checkbox Input",
                "_style"    =>  [
                    "customClass"   =>  [
                        "input"   =>  "filled-in"
                    ]
                ]
            ],
            # Custom range min and max
            [
                "name"      =>  "custom_range_input_min_max",
                "type"      =>  "range",
                "label"     =>  "Custom Range Min and Max Input",
                "select"    =>  [
                    [
                        "value" =>  10
                    ],
                    [
                        "value" =>  90
                    ],
                ]
            ],
            # Custom range with steps
            [
                "name"      =>  "custom_range_input_step",
                "type"      =>  "range",
                "label"     =>  "Custom Range with Step Input",
                "_style"    =>  [
                    "range"     =>  [
                        "step"      =>  10
                    ]
                ]
            ],
            # Today Date
            [
                "name"      =>  "today_date_input",
                "type"      =>  "date",
                "label"     =>  "Current Date Input (today)",
                "default"   =>  "today()"
            ],
            # Previous Date
            [
                "name"      =>  "previous_date_input",
                "type"      =>  "date",
                "label"     =>  "Previous Date Input (yesterday)",
                "default"   =>  "yesterday()"
            ],
            # Next Date
            [
                "name"      =>  "next_date_input",
                "type"      =>  "date",
                "label"     =>  "Next Date Input (tomorrow)",
                "default"   =>  "tomorrow()"
            ],
            # Date with range
            [
                "name"      =>  "next_date_range",
                "type"      =>  "date",
                "label"     =>  "Range Date Input",
                "select"    =>  [
                    [
                        "value" =>  "tomorrow()"
                    ],
                    [
                        "value" =>  "yesterday()"
                    ],
                ]
            ],
            # Custom number min and max
            [
                "name"      =>  "custom_number_input_min_max",
                "type"      =>  "number",
                "label"     =>  "Custom Number Min and Max Input",
                "select"    =>  [
                    [
                        "value" =>  10
                    ],
                    [
                        "value" =>  90
                    ],
                ]
            ],
            # Text input with min and max
            [
                "name"          =>  "text_input_min_max",
                "type"          =>  "text",
                "label"         =>  "Text Input With Min And Max",
                "_style"        =>  [
                    "text"        =>  [
                        "minlength"     =>  2,
                        "maxlength"     =>  10,
                    ]   
                ]
            ],
            # Password
            [
                "name"      =>  "custom_password_input",
                "type"      =>  "password",
                "label"     =>  "Custom Password Input",
                "default"   =>  "Password Visible",
                "_style"        =>  [
                    "password"        =>  [
                        "visible"       =>  true,
                    ]   
                ]
            ],
        ]
    ];

    /** @var array BASIC_SIMPLE */
    public const DISABLED_FORM = [
        "id"            =>  "disabled_form",
        "title"         =>  "Disabled Form",
        "entity"        =>  null,
        "onready"       =>  null,
        "reset"         =>  true,
        "items"         =>  [
            # Disabled text input
            [
                "name"      =>  "disabled_text_input",
                "type"      =>  "text",
                "label"     =>  "Disabled Text Input",
                "disabled"  =>  true,
                "default"   =>  "Text"
            ],
            # Disabled checkbox
            [
                "name"      =>  "disabled_checkbox_input",
                "type"      =>  "checkbox",
                "label"     =>  "Disabled Checkbox Input",
                "disabled"  =>  true,
            ],
            # Disabled Simple radio
            [
                "name"      =>  "disabled_radio_input",
                "type"      =>  "radio",
                "label"     =>  "Disabled Radio Input",
                "disabled"  =>  true,
                "select"    =>  [
                    [
                        "label" =>  "Option 1",
                        "value" =>  1
                    ],
                    [
                        "label" =>  "Option 2",
                        "value" =>  2
                    ],
                    [
                        "label" =>  "Option 3",
                        "value" =>  3
                    ],
                ]
            ],
            # Partial Disabled Simple radio
            [
                "name"      =>  "partial_disabled_radio_input",
                "type"      =>  "radio",
                "label"     =>  "Partial Disabled Radio Input",
                "select"    =>  [
                    [
                        "label" =>  "Option 1",
                        "value" =>  1,
                        "disabled"  =>  true,
                    ],
                    [
                        "label" =>  "Option 2",
                        "value" =>  2
                    ],
                    [
                        "label" =>  "Option 3",
                        "value" =>  3,
                        "disabled"  =>  true,
                    ],
                ]
            ],
            # Disabled Simple switch
            [
                "name"      =>  "disabled_switch_input",
                "type"      =>  "switch",
                "label"     =>  "Disabled Switch Input",
                "disabled"  =>  true,
            ],
            # Disabled Simple range
            [
                "name"      =>  "disabled_range_input",
                "type"      =>  "range",
                "label"     =>  "Disabled Range Input",
                "disabled"  =>  true,
            ],
            # Password
            [
                "name"      =>  "password_input",
                "type"      =>  "password",
                "label"     =>  "Password Input",
                "disabled"  =>  true,
            ],
        ]
    ];

    /** @var array READONLY_SIMPLE */
    public const READONLY_FORM = [
        "id"            =>  "readonly_form",
        "title"         =>  "Read Only Form",
        "entity"        =>  null,
        "onready"       =>  null,
        "reset"         =>  true,
        "items"         =>  [
            # Read Only text input
            [
                "name"      =>  "readonly_text_input",
                "type"      =>  "text",
                "label"     =>  "Read Only Text Input",
                "readonly"  =>  true,
                "default"   =>  "Text"
            ],
            # Password
            [
                "name"      =>  "password_input",
                "type"      =>  "password",
                "label"     =>  "Password Input",
                "readonly"  =>  true,
            ],
        ]
    ];

    /** @var array DEFAULT_SIMPLE */
    public const DEFAULT_FORM = [
        "id"            =>  "default_form",
        "title"         =>  "Default Form",
        "entity"        =>  null,
        "onready"       =>  null,
        "reset"         =>  true,
        "items"         =>  [
            # Default text input
            [
                "name"      =>  "default_text_input",
                "type"      =>  "text",
                "label"     =>  "Default Text Input",
                "default"   =>  "Default text"
            ],
            # Default checkbox (True)
            [
                "name"      =>  "default_checkbox_input_true",
                "type"      =>  "checkbox",
                "label"     =>  "Default Checkbox Input (True)",
                "default"   =>  true,
            ],
            # Default checkbox (False)
            [
                "name"      =>  "default_checkbox_input_false",
                "type"      =>  "checkbox",
                "label"     =>  "Default Checkbox Input (False)",
                "default"   =>  false,
            ],
            # Default radio value on item
            [
                "name"      =>  "default_radio_input_item",
                "type"      =>  "radio",
                "label"     =>  "Default Radio Input (On Item)",
                "default"   =>  2,
                "select"    =>  [
                    [
                        "label" =>  "Option 1",
                        "value" =>  1
                    ],
                    [
                        "label" =>  "Option 2",
                        "value" =>  2
                    ],
                    [
                        "label" =>  "Option 3",
                        "value" =>  3
                    ],
                ]
            ],
            # Default radio value on select
            [
                "name"      =>  "default_radio_input_select",
                "type"      =>  "radio",
                "label"     =>  "Default Radio Input (On Select)",
                "select"    =>  [
                    [
                        "label" =>  "Option 1",
                        "value" =>  1
                    ],
                    [
                        "label" =>  "Option 2",
                        "value" =>  2
                    ],
                    [
                        "label"     =>  "Option 3",
                        "value"     =>  3,
                        "default"   =>  true
                    ],
                ]
            ],
            # Default switch (True)
            [
                "name"      =>  "default_switch_input_true",
                "type"      =>  "switch",
                "label"     =>  "Default Switch Input (True)",
                "default"   =>  true
            ],
            # Default switch (False)
            [
                "name"      =>  "default_switch_input_false",
                "type"      =>  "switch",
                "label"     =>  "Default Switch Input (False)",
                "default"   =>  false
            ],
            # Simple range
            [
                "name"      =>  "default_range_input",
                "type"      =>  "range",
                "label"     =>  "Default Range Input",
                "default"   =>  75
            ],
            # Simple number
            [
                "name"      =>  "default_number_input",
                "type"      =>  "number",
                "label"     =>  "Default Number Input",
                "default"   =>  29
            ],
            # Date number
            [
                "name"      =>  "default_date_input",
                "type"      =>  "date",
                "label"     =>  "Default Date Input",
                "default"   =>  "2024-09-26"
            ],
            # Password
            [
                "name"      =>  "password_input",
                "type"      =>  "password",
                "label"     =>  "Password Input",
                "default"   =>  "password"
            ],
        ]
    ];

    /** @var array FORM_REQUIRED */
    public const REQUIRED_FORM = [
        "id"            =>  "required_form",
        "title"         =>  "Required Form",
        "entity"        =>  null,
        "onready"       =>  null,
        "reset"         =>  true,
        "items"         =>  [
            # Required text input
            [
                "name"      =>  "required_text_input",
                "type"      =>  "text",
                "label"     =>  "Required Text Input",
                "required"  =>  true
            ],
            # Required email input
            [
                "name"      =>  "required_email_input",
                "type"      =>  "email",
                "label"     =>  "Required Email Input",
                "required"  =>  true
            ],
            # Simple checkbox
            [
                "name"      =>  "checkbox_input",
                "type"      =>  "checkbox",
                "label"     =>  "Checkbox Input",
            ],
            # Simple radio
            [
                "name"      =>  "radio_input",
                "type"      =>  "radio",
                "label"     =>  "Radio Input",
                "select"    =>  [
                    [
                        "label" =>  "Option 1",
                        "value" =>  1
                    ],
                    [
                        "label" =>  "Option 2",
                        "value" =>  2
                    ],
                    [
                        "label" =>  "Option 3",
                        "value" =>  3
                    ],
                ]
            ],
            # Simple switch
            [
                "name"      =>  "switch_input",
                "type"      =>  "switch",
                "label"     =>  "Switch Input",
            ],
            # Simple range
            [
                "name"      =>  "range_input",
                "type"      =>  "range",
                "label"     =>  "Range Input",
            ],
            # Simple number
            [
                "name"      =>  "number_input",
                "type"      =>  "number",
                "label"     =>  "Number Input",
            ],
            # Date number
            [
                "name"      =>  "date_input",
                "type"      =>  "date",
                "label"     =>  "Date Input",
            ],
            # Password
            [
                "name"      =>  "password_input",
                "type"      =>  "password",
                "label"     =>  "Password Input",
            ],
        ]
    ];

}