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
      return 'far fa-file-zip';
    case 'application/pdf':
      return 'far fa-file-pdf';
    case 'text/plain':
      return 'far fa-file-alt';
    case 'text/x-php':
      return 'far fa-file-code';
    case 'application/msword':
      return 'far fa-file-word';
    case 'audio/mpeg':
      return 'far fa-file-audio';
    default:
      return 'far fa-file';
  }
}
