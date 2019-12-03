import React from 'react';
import PropTypes from 'prop-types';
import { FormattedMessage } from 'react-intl';
import { connect } from 'react-redux';
import { Map } from 'immutable';
import '@deskpro/fa-picker/css/icon-mart.css';
import { FaPicker } from '@deskpro/fa-picker/dist';
import { Label, Input, Button } from '@deskpro/react-components';
import { Detached } from 'DeskPRO/Component/Positioned/Detached';
import { ClickOut } from 'DeskPRO/Component/ClickOut';
import * as actions from '../../Actions/iconPickerActions';
import { iconsSelector } from '../../Selectors/icons';

@connect(state => ({
  icons: iconsSelector(state)
}))
export default class IconPicker extends React.Component {
  static propTypes = {
    icon:     PropTypes.object,
    icons:    PropTypes.object,
    dispatch: PropTypes.func,
  };

  static getDerivedStateFromProps(props) {
    const { icons } = props;
    if (!icons.isEmpty()) {
      const customIcons = icons.get('icons', new Map()).toJS();
      return {
        customIcons: customIcons.map(icon => ({
          name:        icon.name,
          short_names: [icon.name],
          imageUrl:    icon.url,
          keywords:    icon.tags,
          blobId:      icon.blob_id,
        }))
      };
    }
    return null;
  }

  constructor(props) {
    super(props);
    this.container = React.createRef();

    let icon;
    let color;
    let style;

    if (props.icon.urn !== '/') {
      if (props.icon.urn.match('local:blobs')) {
        icon = {
          blobId:   props.icon.urn.replace(/.*:/, ''),
          imageUrl: props.icon.imageUrl,
        };
      } else {
        const iconParts = props.icon.urn.split(':');
        icon = iconParts[iconParts.length - 1];
        style = props.icon.style;
        color = props.icon.color;
      }
    }

    this.state = {
      opened:       false,
      customOpened: false,
      icon,
      style,
      color,
      customIcons:  [],
      file:         {},
      uploading:    false,
      custom:       {
        name:     '',
        keywords: ''
      }
    };
    this.handleAcceptedFiles = this.handleAcceptedFiles.bind(this);
    this.handleCustomInput = this.handleCustomInput.bind(this);
    this.openPicker = this.openPicker.bind(this);
    this.selectIcon = this.selectIcon.bind(this);
    this.removeIcon = this.removeIcon.bind(this);
    this.renderIcon = this.renderIcon.bind(this);
    this.renderInputs = this.renderInputs.bind(this);
    this.renderPicker = this.renderPicker.bind(this);
    this.setCustomIcon = this.setCustomIcon.bind(this);
  }

  componentDidMount() {
    this.props.dispatch(actions.loadIcons());
  }

  setCustomIcon() {
    const { file, custom } = this.state;

    this.setState({
      uploading: true
    });

    const xhr = new XMLHttpRequest();

    const onreadystatechange = () => {
      if (xhr.readyState === XMLHttpRequest.DONE && xhr.status === 200) {
        const { data } = JSON.parse(xhr.response);
        const { customIcons } = this.state;

        const icon = {
          name:        custom.name,
          short_names: [custom.name],
          imageUrl:    data.download_url,
          keywords:    custom.keywords,
          blobId:      data.blob_auth_id,
        };
        customIcons.push(icon);
        this.setState({
          icon,
          uploading:    false,
          customIcons,
          customOpened: false,
        });
      }
    };

    xhr.addEventListener('readystatechange', onreadystatechange, false);
    const formData = new FormData();
    formData.append('file', file);
    formData.append('name', custom.name);
    formData.append('keywords', custom.keywords);
    xhr.open('POST', '/api/v2/custom_icons/upload', true);
    xhr.send(formData);
  }

  handleAcceptedFiles(accepted) {
    const file = accepted[0];
    this.setState({
      file,
      opened:       false,
      customOpened: true,
      custom:       {
        name:     file.name.replace(/\.[^.]+$/, ''),
        keywords: ''
      }
    });
  }

  handleCustomInput(value, name) {
    const { custom } = this.state;
    custom[name] = value;
    this.setState({
      custom
    });
  }

  openPicker() {
    this.setState({
      opened: true
    });
  }

  selectIcon(icon, style, color) {
    if (typeof icon === 'string') {
      this.setState({
        icon:   `fa-${icon}`,
        style:  `fa${style[0]}`,
        color,
        opened: false,
      });
    } else {
      this.setState({
        icon,
        opened: false,
      });
    }
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
    if (typeof icon === 'string') {
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
    return (
      <div className="icon-property">
        <img src={icon.imageUrl} style={{ color }} role="presentation" />
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

    if (typeof icon === 'string') {
      return [
        <input key="urn" type="hidden" name="icon[urn]" value={`urn:deskpro:product:icons:fontawesome:${icon}`} />,
        <input key="style" type="hidden" name="icon[style]" value={style} />,
        <input key="color" type="hidden" name="icon[color]" value={color} />,
      ];
    }
    return [
      <input key="urn" type="hidden" name="icon[urn]" value={`urn:deskpro:local:blobs:${icon.blobId}`} />
    ];
  }

  renderPicker() {
    const { customIcons } = this.state;
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
            onAcceptedFiles={this.handleAcceptedFiles}
            custom={customIcons}
            showCustom
          />
        </ClickOut>
      </Detached>
    );
  }

  renderCustomModal() {
    const { custom, file, uploading } = this.state;
    let image = null;
    if (file.name) {
      image = <img src={URL.createObjectURL(file)} alt={file.name} width={50} />;
    }
    return (
      <Detached
        zIndex={99999}
        positionAt="right top"
        isOpen={this.state.customOpened}
        positionTarget={this.container.current}
      >
        <ClickOut
          onClickOut={() => {
            this.setState({ customOpened: false });
          }}
        >
          <div className="fa_picker_custom_modal">
            {image}
            <Label>Name</Label>
            <Input name="name" value={custom.name} onChange={this.handleCustomInput} required />
            <Label>Keywords</Label>
            <Input name="keywords" value={custom.keywords} onChange={this.handleCustomInput} />
            <br />
            <br />
            <Button onClick={this.setCustomIcon} loading={uploading} >Add</Button>
          </div>
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
        {this.renderCustomModal()}
      </div>
    );
  }
}
