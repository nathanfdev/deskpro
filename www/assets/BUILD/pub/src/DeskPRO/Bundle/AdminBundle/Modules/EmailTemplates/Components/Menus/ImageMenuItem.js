import React, { PropTypes } from 'react';
import { PopUp } from 'DeskPRO/Component/Semantic/PopUp';
import { MenuItem } from 'DeskPRO/Component/Semantic/Menu';

class ImageMenuItem extends React.Component {
  static propTypes    = {
    url:       PropTypes.string,
    label:     PropTypes.string,
    onClick:   PropTypes.func,
    className: PropTypes.string
  };
  static defaultProps = {
    onClick() {},
  };

  componentWillUnmount() {
    this.cancelToolTip();
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
          label={this.props.label}
          src={this.props.url}
          className={this.props.className}
          onClick={this.props.onClick}
          onMouseOver={this.templateToolTip}
          onMouseOut={this.cancelToolTip}
        />
      </PopUp>
    );
  }
}
export default ImageMenuItem;
