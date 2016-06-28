export function getImageDataUrl(imgUrl, callback) {
  const img = new Image();

  img.setAttribute('crossOrigin', 'anonymous');
  img.onload = () => {
    const canvas = document.createElement('canvas');
    canvas.width = img.width;
    canvas.height = img.height;

    const ctx = canvas.getContext('2d');
    ctx.drawImage(img, 0, 0);

    const dataURL = canvas.toDataURL('image/png');
    callback(dataURL);
  };

  img.src = imgUrl;
}

export function dataUrlToBlob(dataUrl) {
  const byteString = atob(dataUrl.split(',')[1]);
  const ab = new ArrayBuffer(byteString.length);
  const ia = new Uint8Array(ab);

  for (let num = 0; num < byteString.length; num++) {
    ia[num] = byteString.charCodeAt(num);
  }

  return new Blob([ab]);
}
