export function filenameMaxLength(filename = '', maxLength = 25) {
  const fullName = String(filename);
  const dotIndex = fullName.lastIndexOf('.');
  const extension = fullName.substring(dotIndex + 1);
  const suffix = '... ';

  let name = fullName.substring(0, dotIndex);
  const shortName = name.substring(0, maxLength - (extension.length + 1));
  if (name !== shortName) {
    name = shortName.substring(0, shortName.length - suffix.length) + suffix;
  }

  return `${name}.${extension}`;
}

export function getFileIcon(contentType) {
  switch (contentType) {
    case 'application/zip':
    case 'application/x-gzip':
      return 'fa-file-zip-o';
    case 'application/pdf':
      return 'fa-file-pdf-o';
    case 'text/plain':
      return 'fa-file-text-o';
    case 'text/x-php':
      return 'fa-file-code-o';
    case 'application/msword':
      return 'fa-file-word-o';
    case 'audio/mpeg':
      return 'fa-file-audio-o';
    default:
      return 'fa-file-o';
  }
}
