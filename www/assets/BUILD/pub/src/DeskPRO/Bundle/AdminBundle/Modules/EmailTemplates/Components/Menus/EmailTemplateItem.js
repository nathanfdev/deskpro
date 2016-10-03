import React, { PropTypes } from 'react';
import { PopUp } from 'DeskPRO/Component/Semantic/PopUp';
import { MenuItem } from 'DeskPRO/Component/Semantic/Menu';

class EmailTemplateItem extends React.Component {
  static propTypes    = {
    desc:      PropTypes.string,
    label:     PropTypes.string,
    icon:      PropTypes.string,
    onClick:   PropTypes.func,
    className: PropTypes.string
  };
  static defaultProps = {
    onClick() {},
  };

  templateToolTip = () => {
    this.toolTipTimeOutId = setTimeout(() => {
      this.templatePopup.openPopup();
    }, 500);
  };

  cancelToolTip = () => {
    window.clearTimeout(this.toolTipTimeOutId);
    this.toolTipTimeOutId = undefined;
    this.templatePopup.closePopup();
  };

  render() {
    return (
      <PopUp
        positionMy="left top-15px"
        positionAt="right top"
        zIndex={99999}
        content={this.props.desc}
        ref={(c) => { this.templatePopup = c; }}
        className="template-item"
        autoOpen={false}
      >
        <MenuItem
          label={this.props.label}
          icon={this.props.icon}
          className={this.props.className}
          onClick={this.props.onClick}
          onMouseOver={this.templateToolTip}
          onMouseOut={this.cancelToolTip}
        />
      </PopUp>
    );
  }
}
export default EmailTemplateItem;
