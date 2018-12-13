import PropTypes from 'prop-types';
import React from 'react';
import { PopUp } from 'DeskPRO/Component/Semantic/PopUp';
import { MenuItem } from 'DeskPRO/Component/Semantic/Menu';

class ImageMenuItem extends React.Component {
  static propTypes    = {
    url:          PropTypes.string,
    label:        PropTypes.string,
    onClick:      PropTypes.func,
    deleteFile:   PropTypes.func,
    downloadFile: PropTypes.func,
    className:    PropTypes.string
  };
  static defaultProps = {
    onClick() {},
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
    if (this.templatePopup) {
      this.templatePopup.closePopup();
    }
  };

  render() {
    let positionMy;
    let positionAt;
    if (window.document.documentElement.clientWidth > 1750) {
      positionMy = 'left top-15px';
      positionAt = 'right top';
    } else {
      positionMy = 'right top-15px';
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
        </MenuItem>
      </PopUp>
    );
  }
}
export default ImageMenuItem;
