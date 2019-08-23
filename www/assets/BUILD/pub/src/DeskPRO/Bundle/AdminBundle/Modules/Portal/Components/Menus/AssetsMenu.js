import PropTypes from 'prop-types';
import React from 'react';
import { connect } from 'react-redux';
import classNames from 'classnames';
import { Progress, ProgressBar } from '@deskpro/react-components';
import { MenuWrapper, Menu, MenuItem } from 'DeskPRO/Component/Semantic/Menu';
import { MimeIcon } from 'DeskPRO/Component/Semantic/Icon';
import ImageMenuItem from 'DeskPRO/Component/CMEditor/Menus/ImageMenuItem';
import MediaDropZone from 'DeskPRO/Component/CMEditor/Menus/MediaDropZone';
import * as actions from '../../Actions/templatesActions';

@connect(state => ({
  portalEditor: state.Portal.templates
}))
export class AssetsMenuContainer extends React.Component {
  static propTypes = {
    dispatch:          PropTypes.func,
    portalEditor:      PropTypes.object.isRequired,
    insertAsset:       PropTypes.func,
    insertAssetAsLink: PropTypes.func,
  };
  static defaultProps = {
    insertAsset() {},
    insertAssetAsLink() {},
  };

  constructor(props) {
    super(props);
    this.state = {
      uploading: false,
      progress:  0,
    };
  }

  onSend = () => {
    this.setState({
      uploading: true,
      progress:  0
    });
  };

  onFail = () => {
    this.setState({
      uploading: false
    });
  };

  handleProgress = (progress) => {
    this.setState({
      progress: progress * 100
    });
  };

  reloadFiles = () => {
    this.props.dispatch(actions.loadAssets()).then(() => {
      this.setState({
        uploading: false
      });
    });
  };

  deleteFile = (e, file) => {
    e.stopPropagation();
    if (confirm('Are you sure you want to delete this file ?')) {
      this.props.dispatch(actions.deleteAsset(file.get('id'))).then(() => {
        this.props.dispatch(actions.loadAssets());
      });
    }
  };

  downloadFile = (e, file) => {
    e.stopPropagation();
    window.location.replace(`${file.get('url')}?dl=1`);
  };

  render() {
    let assets = null;
    if (this.props.portalEditor) {
      assets = this.props.portalEditor.get('assets');
    }
    return (<AssetsMenu
      assets={assets}
      reloadFiles={this.reloadFiles}
      onFail={this.onFail}
      onSend={this.onSend}
      onProgress={this.handleProgress}
      deleteFile={this.deleteFile}
      downloadFile={this.downloadFile}
      insertAsset={this.props.insertAsset}
      insertAssetAsLink={this.props.insertAssetAsLink}
      uploading={this.state.uploading}
      progress={this.state.progress}
    />);
  }
}

export class AssetsMenu extends React.Component {
  static propTypes = {
    assets:            PropTypes.node,
    reloadFiles:       PropTypes.func,
    onFail:            PropTypes.func,
    onSend:            PropTypes.func,
    onProgress:        PropTypes.func,
    deleteFile:        PropTypes.func,
    downloadFile:      PropTypes.func,
    insertAsset:       PropTypes.func,
    insertAssetAsLink: PropTypes.func,
    uploading:         PropTypes.bool,
    progress:          PropTypes.number,
  };

  componentWillMount() {
    const button = window.document.getElementsByClassName('media-button')[0];
    this.coverWidth = button.offsetWidth;
  }

  getAssets = () => {
    if (!this.props.assets) {
      return null;
    }
    return this.props.assets.valueSeq().map((file, key) => {
      if (file.get('mime_type').match(/^image/)) {
        return (
          <ImageMenuItem
            key={`file${key}`}
            label={file.get('name')}
            url={file.get('url')}
            position="left"
            onClick={() => this.props.insertAsset(file)}
            deleteFile={e => this.props.deleteFile(e, file, 'inline-image')}
            downloadFile={e => this.props.downloadFile(e, file)}
            insertAsLink={e => this.props.insertAssetAsLink(e, file)}
          />
        );
      }
      return (
        <MenuItem
          key={`file${key}`}
          label={file.get('name')}
          icon={MimeIcon.getIcon(file.get('mime_type'))}
          onClick={e => this.props.insertAssetAsLink(e, file)}
        >
          <i onClick={e => this.props.deleteFile(e, file, 'attachment')} className="remove icon" title="Remove" />
          <i onClick={e => this.props.downloadFile(e, file)} className="download icon" title="Download" />
          <i onClick={e => this.props.insertAssetAsLink(e, file)} className="linkify icon" title="Insert Link" />
        </MenuItem>
      );
    });
  };

  getUploadUrl = () => '/portal/api/style/edit-theme-set/assets';

  render() {
    return (
      <div className="media-drop-down assets-menu">
        <div className="menu-button-cover" style={{ width: this.coverWidth + 20 }} />
        <div className={classNames('ui dimmer', { active: this.props.uploading })}>
          <div className="ui text loader">Uploading file</div>
          <Progress size="large" type="primary" style={{ height: '5px' }}>
            <ProgressBar percent={this.props.progress} />
          </Progress>
        </div>
        <MediaDropZone
          icon="attach"
          type="attachment"
          onSuccess={this.props.reloadFiles}
          onSend={this.props.onSend}
          onFail={this.props.onFail}
          onProgress={this.props.onProgress}
          getUploadUrl={this.getUploadUrl}
        />
        <MenuWrapper className="files">
          <Menu>
            {this.getAssets()}
          </Menu>
        </MenuWrapper>
      </div>
    );
  }
}
