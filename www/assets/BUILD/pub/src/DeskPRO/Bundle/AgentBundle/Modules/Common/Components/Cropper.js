import PropTypes from 'prop-types';
import React from 'react';
import jQuery from 'jquery';
import 'cropper';

export class Cropper extends React.Component {

  static propTypes = {
    // react cropper options
    crossOrigin: PropTypes.string,
    src:         PropTypes.string,
    alt:         PropTypes.string,

    // cropper options
    aspectRatio:        PropTypes.number,
    crop:               PropTypes.func,
    preview:            PropTypes.string,
    strict:             PropTypes.bool,
    responsive:         PropTypes.bool,
    checkImageOrigin:   PropTypes.bool,
    background:         PropTypes.bool,
    modal:              PropTypes.bool,
    guides:             PropTypes.bool,
    highlight:          PropTypes.bool,
    autoCrop:           PropTypes.bool,
    autoCropArea:       PropTypes.number,
    dragCrop:           PropTypes.bool,
    movable:            PropTypes.bool,
    cropBoxMovable:     PropTypes.bool,
    cropBoxResizable:   PropTypes.bool,
    doubleClickToggle:  PropTypes.bool,
    zoomable:           PropTypes.bool,
    mouseWheelZoom:     PropTypes.bool,
    touchDragZoom:      PropTypes.bool,
    rotatable:          PropTypes.bool,
    minContainerWidth:  PropTypes.number,
    minContainerHeight: PropTypes.number,
    minCanvasWidth:     PropTypes.number,
    minCanvasHeight:    PropTypes.number,
    minCropBoxWidth:    PropTypes.number,
    minCropBoxHeight:   PropTypes.number,
    build:              PropTypes.func,
    built:              PropTypes.func,
    dragstart:          PropTypes.func,
    dragmove:           PropTypes.func,
    dragend:            PropTypes.func,
    zoomin:             PropTypes.func,
    zoomout:            PropTypes.func
  };

  static defaultProps = {
    src: null
  };

  componentDidMount() {
    var options = {};
    for (var prop in this.props) {
      if (!this.props.hasOwnProperty(prop)) {
        continue;
      }
      if (prop !== 'src' && prop !== 'alt' && prop !== 'crossOrigin') {
        options[prop] = this.props[prop];
      }
    }
    this.$img = jQuery(this.refs.img);
    this.$img.cropper(options);
  }

  componentWillReceiveProps(nextProps) {
    if (nextProps.src !== this.props.src) {
      this.replace(nextProps.src);
    }
    if (nextProps.aspectRatio !== this.props.aspectRatio) {
      this.setAspectRatio(nextProps.aspectRatio);
    }
  }

  componentWillUnmount() {
    if (this.$img) {
      // Destroy the cropper, this makes sure events such as resize are cleaned up and do not leak
      this.$img.cropper('destroy');
      // While we're at it remove our reference to the jQuery instance
      delete this.$img;
    }
  }

  getContainerData() {
    return this.$img.cropper('getContainerData');
  }

  getImageData() {
    return this.$img.cropper('getImageData');
  }

  getCanvasData() {
    return this.$img.cropper('getCanvasData');
  }

  setCanvasData(data) {
    return this.$img.cropper('setCanvasData', data);
  }

  getCropBoxData() {
    return this.$img.cropper('getCropBoxData');
  }

  setCropBoxData(data) {
    return this.$img.cropper('setCropBoxData', data);
  }

  getCroppedCanvas(options) {
    return this.$img.cropper('getCroppedCanvas', options);
  }

  setAspectRatio(aspectRatio) {
    return this.$img.cropper('setAspectRatio', aspectRatio);
  }

  setDragMode() {
    return this.$img.cropper('setDragMode');
  }

  getData() {
    return this.$img.cropper('getData');
  }

  move(offsetX, offsetY) {
    return this.$img.cropper('move', offsetX, offsetY);
  }

  zoom(ratio) {
    return this.$img.cropper('zoom', ratio);
  }

  rotate(degree) {
    return this.$img.cropper('rotate', degree);
  }

  enable() {
    return this.$img.cropper('enable');
  }

  disable() {
    return this.$img.cropper('disable');
  }

  reset() {
    return this.$img.cropper('reset');
  }

  clear() {
    return this.$img.cropper('clear');
  }

  replace(url) {
    return this.$img.cropper('replace', url);
  }

  on(eventname, callback) {
    return this.$img.on(eventname, callback);
  }

  render() {
    return (
      <div {...this.props} src={null} crossOrigin={null} alt={null}>
        <img
          crossOrigin={this.props.crossOrigin}
          ref="img"
          src={this.props.src}
          alt={this.props.alt === undefined ? 'picture' : this.props.alt}
          style={{ opacity: 0 }}
        />
      </div>
    );
  }
}
