import EmailTemplates_templates                           from "./Modules/EmailTemplates/Reducers/templates.js";
import Voice_numbers                                      from "./Modules/Voice/Reducers/numbers.js";
import Voice_settings                                     from "./Modules/Voice/Reducers/settings.js";

export default {
  "EmailTemplates": {
    "templates":                                          EmailTemplates_templates,
  },
  "Voice": {
    "numbers":                                            Voice_numbers,
    "settings":                                           Voice_settings,
  },
};
