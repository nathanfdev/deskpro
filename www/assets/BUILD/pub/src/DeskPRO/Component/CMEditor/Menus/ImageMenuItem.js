import PropTypes from 'prop-types';
import React from 'react';
import { PopUp } from '../../Semantic/PopUp';
import { MenuItem } from '../../Semantic/Menu';

class ImageMenuItem extends React.Component {
  static propTypes    = {
    url:          PropTypes.string,
    label:        PropTypes.string,
    position:     PropTypes.string,
    onClick:      PropTypes.func,
    deleteFile:   PropTypes.func,
    downloadFile: PropTypes.func,
    insertAsLink: PropTypes.func,
    className:    PropTypes.string
  };
  static defaultProps = {
    onClick() {},
    insertAsLink: null,
    position:     'right'
  };

  componentWillUnmount() {
    this.cancelToolTip();
  }

  getImage() {
    const { url } = this.props;
    if (url) {
      return <img src={url} role="presentation"  />;
    }
    return null;
  }

  templateToolTip = () => {
    this.toolTipTimeOutId = setTimeout(() => {
      if (this.templatePopup) {
        this.templatePopup.openPopup();
      }
    }, 500);
  };

  cancelToolTip = () => {
    if (this.toolTipTimeOutId) {
      window.clearTimeout(this.toolTipTimeOutId);
      this.toolTipTimeOutId = undefined;
    }
    setTimeout(() => {
      if (this.templatePopup) {
        this.templatePopup.closePopup();
      }
    }, 500);
  };

  render() {
    let positionMy;
    let positionAt;
    if (window.document.documentElement.clientWidth > 1750 && this.props.position === 'right') {
      positionMy = 'left top-12px';
      positionAt = 'right top';
    } else {
      positionMy = 'right top-12px';
      positionAt = 'left top';
    }
    return (
      <PopUp
        positionMy={positionMy}
        positionAt={positionAt}
        zIndex={99999}
        content={<img src={this.props.url} role="presentation" />}
        ref={(c) => { this.templatePopup = c; }}
        className="image-menu-item"
        autoOpen={false}
      >
        <MenuItem
          className={this.props.className}
          onClick={this.props.onClick}
          onMouseOver={this.templateToolTip}
          onMouseOut={this.cancelToolTip}
        >
          {this.getImage()}
          <span className="filename" title={this.props.label}>{this.props.label}</span>
          <i onClick={this.props.downloadFile} className="download icon" title="Download" />
          <i onClick={this.props.deleteFile} className="remove icon" title="Remove" />
          {this.props.insertAsLink && <i onClick={this.props.insertAsLink} className="linkify icon" title="Insert Link" />}
        </MenuItem>
      </PopUp>
    );
  }
}
export default ImageMenuItem;
