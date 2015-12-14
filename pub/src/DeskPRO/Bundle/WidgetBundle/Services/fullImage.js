export function openFullImage(downloadUrl) {
  const image = document.createElement('image');
  image.src = downloadUrl;

  const width = image.naturalWidth < 800 ? image.naturalWidth : 800;
  const height = image.naturalHeight < 800 ? image.naturalHeight : 800;

  const left = (screen.width / 2) - (width / 2);
  const top = (screen.height / 2) - (height / 2);

  window.open(
    downloadUrl,
    'Image',

    `width=${width},height=${height},left=${left},top=${top},` +
    `resizable=1,directories=0,titlebar=0,location=0,status=0,toolbar=0,menubar=0`
  );
}
