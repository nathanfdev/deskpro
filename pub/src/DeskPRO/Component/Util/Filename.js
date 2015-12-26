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
