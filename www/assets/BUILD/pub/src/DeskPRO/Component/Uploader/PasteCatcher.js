import PropTypes from 'prop-types';
import React from 'react';
import { getImageDataUrl, dataUrlToBlob } from 'DeskPRO/Component/Util/Blob';
import $ from 'jquery';

export const clipboardHasImages = (clipboardData) => {
  if (!clipboardData || !clipboardData.items) {
    return false;
  }

  for (let i = 0; i < clipboardData.items.length; i += 1) {
    const item = clipboardData.items[i];

    if (item.kind === 'file' && item.type.indexOf('image') !== -1) {
      return true;
    }
  }

  return false;
};
export const clipboardIEHasImages = (clipboardData) => {
  if (!clipboardData || !clipboardData.files) {
    return false;
  }

  for (let i = 0; i < clipboardData.files.length; i += 1) {
    const file = clipboardData.files[i];

    if (file.type.indexOf('image') !== -1) {
      return true;
    }
  }
  return false;
};

export const getBlobsFromItems = (items, onPasteImage) => {
  if (!items) {
    return;
  }

  for (let i = 0; i < items.length; i += 1) {
    const item = items[i];

    if (item.kind === 'file' && item.type.indexOf('image') !== -1) {
      const blob = item.getAsFile();
      const urlObj = window.URL || window.webkitURL;
      const imgUrl = urlObj.createObjectURL(blob);

      onPasteImage(blob, imgUrl, item.type);
    }
  }
};

export const getBlobsFromIEItems = (files, event, onPasteImage) => {
  if (!files) {
    return;
  }

  for (let i = 0; i < files.length; i += 1) {
    const file = files[i];

    if (file.type.indexOf('image') !== -1) {
      const url = URL.createObjectURL(file);
      const reader = new window.FileReader();

      reader.onloadend = function () {
        const base64Image = reader.result;
        const blob = dataUrlToBlob(base64Image);
        onPasteImage(blob, url, file.type);
      };

      reader.readAsDataURL(file);
    }
  }
};

export const getBlobsFromHtml = (html, onPasteImage) => {
  const regex = /<img.*?src=['"](.*?)['"].*?>/;
  const callback = (dataUrl) => {
    const blob = dataUrlToBlob(dataUrl);
    onPasteImage(blob, dataUrl, 'image/png');
  };

  const match = regex.exec(html);
  if (match) {
    getImageDataUrl(match[1], callback);
  }
};

export const getBlobFromUrl = (dataUrl, onPasteImage) => {
  const blob = dataUrlToBlob(dataUrl);
  onPasteImage(blob, dataUrl, 'image/jpeg');
};

export class PasteCatcher extends React.Component {

  static propTypes = {
    context:      PropTypes.object,
    onPasteImage: PropTypes.func
  };

  componentDidMount() {
    const { context = document } = this.props;
    $(context).on('paste', this.onPaste);
  }

  componentWillUnmount() {
    const { context = document } = this.props;
    $(context).off('paste', this.onPaste);
  }

  onPaste = (event) => {
    const { onPasteImage } = this.props;
    const clipboardData = event.originalEvent.clipboardData;
    if (!clipboardData) {
      return;
    }

    const items = clipboardData.items;
    if (items) {
      getBlobsFromItems(items, onPasteImage);
    } else {
      getBlobsFromHtml(clipboardData.getData('text/html'), onPasteImage);
    }
  };

  render() {
    return null;
  }
}
