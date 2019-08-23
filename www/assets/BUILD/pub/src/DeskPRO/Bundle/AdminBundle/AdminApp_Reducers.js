import EmailTemplates_templates                           from "./Modules/EmailTemplates/Reducers/templates.js";
import Portal_templates                                   from "./Modules/Portal/Reducers/templates.js";
import Voice_settings                                     from "./Modules/Voice/Reducers/settings.js";

export default {
  "EmailTemplates": {
    "templates":                                          EmailTemplates_templates,
  },
  "Portal": {
    "templates":                                          Portal_templates,
  },
  "Voice": {
    "settings":                                           Voice_settings,
  },
};
