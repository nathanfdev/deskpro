import EmailTemplates_emailTemplates                      from "./Modules/EmailTemplates/Reducers/emailTemplates.js";
import Voice_numbers                                      from "./Modules/Voice/Reducers/numbers.js";
import Voice_settings                                     from "./Modules/Voice/Reducers/settings.js";

export default {
  "EmailTemplates": {
    "emailTemplates":                                     EmailTemplates_emailTemplates,
  },
  "Voice": {
    "numbers":                                            Voice_numbers,
    "settings":                                           Voice_settings,
  },
};
