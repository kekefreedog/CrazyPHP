/**
 * Normalize optional Number filter bounds for both template modes.
 * @package kzarshenas/crazyphp
 */
module.exports = function(config) {

    config = config && typeof config === "object" ? config : {};
    const owns = key => Object.prototype.hasOwnProperty.call(config, key);
    const enabled = ["min", "max", "start", "end", "minLabel", "maxLabel"].some(owns);
    const result = {enabled};

    // Explicit bounds override legacy values, including an explicit null.
    for(const [bound, alias, fallback] of [["min", "start", "Min"], ["max", "end", "Max"]]){

        const setting = owns(bound) ? config[bound] : config[alias];
        const object = setting && typeof setting === "object";
        const value = object ? setting.value : setting;
        result[bound] = {
            label: String((object ? setting.label : null) ?? config[bound + "Label"] ?? fallback),
            value: (typeof value === "number" || typeof value === "string") && String(value).trim() !== "" && Number.isFinite(Number(value)) ? String(Number(value)) : "",
        };

    }

    const min = result.min.value;
    const max = result.max.value;
    result.value = min !== "" && max !== "" ? `=[${min}:${max}]` : min !== "" ? `>=${min}` : max !== "" ? `<=${max}` : "";
    return result;

};
