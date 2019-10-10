import React from 'react';
import PropTypes from 'prop-types';
import { FormattedMessage } from 'react-intl';
import '@deskpro/fa-picker/css/icon-mart.css';
import { FaPicker } from '@deskpro/fa-picker';
import { Detached } from 'DeskPRO/Component/Positioned/Detached';
import { ClickOut } from 'DeskPRO/Component/ClickOut';


export default class IconPicker extends React.PureComponent {
  static propTypes = {
    icon: PropTypes.object,
  };

  constructor(props) {
    super(props);
    this.container = React.createRef();

    let icon;
    let color;
    let style;

    if (props.icon.urn !== '/') {
      const iconParts = props.icon.urn.split(':');
      icon = iconParts[iconParts.length - 1];
      style = props.icon.style;
      color = props.icon.color;
    }

    this.state = {
      opened: false,
      icon,
      style,
      color
    };
    this.openPicker = this.openPicker.bind(this);
    this.selectIcon = this.selectIcon.bind(this);
    this.removeIcon = this.removeIcon.bind(this);
    this.renderIcon = this.renderIcon.bind(this);
    this.renderInputs = this.renderInputs.bind(this);
    this.renderPicker = this.renderPicker.bind(this);
  }

  openPicker() {
    this.setState({
      opened: true
    });
  }

  selectIcon(icon, style, color) {
    this.setState({
      icon:   `fa-${icon}`,
      style:  `fa${style[0]}`,
      color,
      opened: false,
    });
  }

  removeIcon() {
    this.setState({
      icon:  null,
      style: null,
      color: null
    });
  }

  renderIcon() {
    const { icon, style, color } = this.state;
    if (!icon) {
      return null;
    }
    const className = `${style} ${icon}`;
    return (
      <div className="icon-property">
        <i className={className} style={{ color }} />
        <i
          className="fas fa-times delete"
          title="Remove the icon"
          onClick={this.removeIcon}
          style={{ cursor: 'pointer' }}
        />
      </div>
    );
  }

  renderInputs() {
    const { icon, style, color } = this.state;
    if (!icon) {
      return null;
    }

    return [
      <input key="urn" type="hidden" name="icon[urn]" value={`urn:deskpro:product:icons:fontawesome:${icon}`} />,
      <input key="style" type="hidden" name="icon[style]" value={style} />,
      <input key="color" type="hidden" name="icon[color]" value={color} />,
    ];
  }

  renderPicker() {
    return (
      <Detached
        zIndex={99999}
        positionAt="right top"
        isOpen={this.state.opened}
        positionTarget={this.container.current}
      >
        <ClickOut
          onClickOut={() => {
            this.setState({ opened: false });
          }}
        >
          <FaPicker
            color="#00F"
            onSelect={this.selectIcon}
          />
        </ClickOut>
      </Detached>
    );
  }

  render() {
    return (
      <div>
        {this.renderIcon()}
        {this.renderInputs()}
        <button className="dp-btn btn-default" type="button" onClick={this.openPicker} ref={this.container}>
          <FormattedMessage id="agent.publish.pick_icon" />
        </button>
        {this.renderPicker()}
      </div>
    );
  }
}
