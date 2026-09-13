/**
 * Example Card 2
 *
 * Copy this directory into app/Environment/Component and register example-card-2.
 *
 * @package    kzarshenas/crazyphp
 * @author     kekefreedog <kevin.zarshenas@gmail.com>
 * @copyright  2022-2026 Kévin Zarshenas
 */

/**
 * Dependances
 */
import { Crazycomponent2, type Crazycomponent2Properties } from "crazyphp";

/**
 * Example Card 2
 *
 * Demonstrate typed properties, slots, event cleanup, and optional Shadow DOM.
 */
export default class ExampleCard2 extends Crazycomponent2<CardValues> {

    /** Static Parameters
     ******************************************************
     */

    /** @var options Set shadow to true to enable an open shadow root */
    static options = { shadow: false };

    /** @var template Compiled Handlebars template */
    static template = require("./template.hbs");

    /** @var styles Compiled SCSS returned as a css-loader export */
    static styles = require("!!css-loader!sass-loader!./style.scss");

    /** @var properties Observed attributes, defaults, and reflection options */
    static properties = {
        label: { type: "string", default: "Example card" },
        count: { type: "number", default: 0, reflect: true },
    } satisfies Crazycomponent2Properties;

    /** Methods | Events
     ******************************************************
     */

    /**
     * Post Render
     *
     * Bind the increment button after each render and release its listener
     * before the next render or disconnection.
     *
     * @return void
     */
    public postRender(): void {

        // Query the active rendering root, in either light DOM or shadow mode.
        const button = this.renderRoot.querySelector<HTMLButtonElement>("button")!;

        // Update the typed value; reflection and rerendering are handled by the base class.
        const increment = () => this.setProperty("count", this.getProperty("count") + 1);
        button.addEventListener("click", increment);

        // Release the handler when this generated button is replaced.
        this.onCleanup(() => button.removeEventListener("click", increment));

    }

}

/** Interface
 ******************************************************
 */

/** Typed values matching the static property schema. */
interface CardValues {
    /** Heading shown when no named heading content is supplied. */
    label: string;
    /** Counter reflected to the count HTML attribute. */
    count: number;
}
