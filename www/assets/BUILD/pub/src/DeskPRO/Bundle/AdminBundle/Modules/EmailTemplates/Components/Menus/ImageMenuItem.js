import React, { PropTypes } from 'react';
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
    return (
      <PopUp
        positionMy="left top-15px"
        positionAt="right top"
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
          {this.props.label}
          <i onClick={this.props.downloadFile} className="download icon" title="Download" />
          <i onClick={this.props.deleteFile} className="remove icon" title="Remove" />
        </MenuItem>
      </PopUp>
    );
  }
}
export default ImageMenuItem;
