/**
 * Reproduction of react-intl's getIntlMessage() mixin method.
 * @param object messages is the list of messsages.
 * @param path is the path for which the translation is required.
 */
export default function getIntlMessage(messages, path) {
  if(!messages) {
    return path;
  }
  if(messages.messages) { // We still want this to work if we're given the whole translations object.
    messages = messages.messages;
  }
  const path_parts = path.split('.');
  
  let message = '';
  try {
    message = path_parts.reduce((obj, path_part) => obj[path_part], messages);
  } finally {
    if(message === undefined) {
      throw new ReferenceError("Couldn't find Intl message: " + path);
    }
  }
  
  return message;
}
