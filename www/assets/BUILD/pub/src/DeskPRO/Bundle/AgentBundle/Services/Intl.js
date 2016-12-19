/**
 * Reproduction of react-intl's getIntlMessage() mixin method.
 * @param messagesList object is the list of messsages.
 * @param path is the path for which the translation is required.
 */
export function getIntlMessage(messagesList, path) {
  if (!messagesList) {
    return path;
  }
  let messages;
  if (messagesList.messages) { // We still want this to work if we're given the whole translations object.
    messages = messagesList.messages;
  }
  const pathParts = path.split('.');

  let message = '';
  try {
    message = pathParts.reduce((obj, pathPart) => obj[pathPart], messages);
  } finally {
    if (message === undefined) {
      throw new ReferenceError("Couldn't find Intl message: " + path);
    }
  }

  return message;
}
