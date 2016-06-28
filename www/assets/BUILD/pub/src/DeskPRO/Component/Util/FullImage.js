let fullImageWindow;

export function openFullImage(imageNode, maxWidth = 800, maxHeight = 800) {
  const width = imageNode.naturalWidth < maxWidth ? imageNode.naturalWidth : maxWidth;
  const height = imageNode.naturalHeight < maxHeight ? imageNode.naturalHeight : maxHeight;

  const left = (screen.width / 2) - (width / 2);
  const top = (screen.height / 2) - (height / 2);

  if (fullImageWindow) {
    fullImageWindow.close();
  }

  fullImageWindow = window.open(
    imageNode.src,
    'Image',

    `width=${width},height=${height},left=${left},top=${top},` +
    'resizable=1,directories=0,titlebar=0,location=0,status=0,toolbar=0,menubar=0'
  );
}
