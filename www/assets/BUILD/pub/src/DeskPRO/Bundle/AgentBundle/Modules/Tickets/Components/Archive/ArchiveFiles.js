import PropTypes from 'prop-types';
import React from 'react';
import { connect } from 'react-redux';
import classNames from 'classnames';
import { pureRender } from 'Ampliflux';
import { List, ListElement } from 'DeskPRO/Component/Semantic/List';
import { Detached } from 'DeskPRO/Component/Positioned/Detached';
import { ClickOut } from 'DeskPRO/Component/ClickOut';
import * as actions from '../../Actions/archiveActions';
import { filesSelector } from '../../Selectors/archive';

@connect(state => ({
  files: filesSelector(state)
}))

@pureRender
export class ArchiveFilesContainer extends React.Component {
  static propTypes = {
    authId:   PropTypes.string,
    dispatch: PropTypes.func,
    files:    PropTypes.object.isRequired
  };

  getLink = (element) => {
    const { authId } = this.props;
    if (element.size > 100000000) {
      return null;
    }
    return `/api/v2/blobs/${authId}/download/${element.name}`;
  };

  loadFiles = () => {
    const { authId, files } = this.props;

    if (!files.get(authId)) {
      this.props.dispatch(actions.loadFiles(authId));
    }
  };

  render() {
    const { files, authId } = this.props;
    let filesList = {};
    const list = files.filter(x => x.get('id') === authId);
    if (list.size) {
      filesList = list.first().get('files');
    }
    return (
      <ArchiveFiles
        list={filesList}
        getLink={this.getLink}
        loadFiles={this.loadFiles}
      />
    );
  }
}

export class ArchiveFiles extends React.Component {
  static propTypes = {
    list:      PropTypes.object,
    getLink:   PropTypes.func,
    loadFiles: PropTypes.func
  };

  static defaultProps = {
    list: {},
    getLink() {
    }
  };

  static unflattenList(list, index, level) {
    const tree = [];
    let localIndex = index;

    while (localIndex < list.size) {
      const element = list.get(localIndex).toObject();
      const path = list.get(localIndex).get('name').split('/');
      element.dir = path[path.length - 1] === '';

      if (!element.dir) {
        if (level > path.length - 1) {
          return tree;
        }
        element.filename = path[path.length - 1];
        tree.push(element);
      } else {
        if (level > path.length - 2) {
          return tree;
        }
        element.filename = path[path.length - 2];
        element.children = ArchiveFiles.unflattenList(list, localIndex + 1, level + 1);
        tree.push(element);
        localIndex += ArchiveFiles.recursiveLength(element.children);
      }
      localIndex += 1;
    }
    return tree;
  }

  static recursiveLength(array) {
    let length = 0;
    for (const e of array) {
      length += 1;
      if (e.children) {
        length += ArchiveFiles.recursiveLength(e.children);
      }
    }
    return length;
  }

  constructor(props) {
    super(props);
    this.state = {
      tree: ArchiveFiles.unflattenList(this.props.list, 0, 0),
      open: false
    };
  }

  componentWillReceiveProps(nextProps) {
    if (nextProps.list !== this.props.list) {
      this.setState({
        tree: ArchiveFiles.unflattenList(nextProps.list, 0, 0)
      });
    }
  }

  getFileList = () => {
    if (this.state.tree.length === 0) {
      return <div className="ui loader mini inline active" />;
    }
    return this.renderTree(this.state.tree);
  };

  toggleFiles = () => {
    if (!this.state.open) {
      this.props.loadFiles();
    }
    this.setState({
      open: !this.state.open
    });
  };

  renderTree = (tree) => {
    if (tree) {
      return (
        <List>
          {tree.map((element) => {
            let label = element.filename;
            if (element.filesize_readable) {
              label = `${label} (${element.filesize_readable})`;
            }
            return (
              <ListElement
                icon={element.dir ? 'folder' : 'file'}
                href={element.dir ? '' : this.props.getLink(element)}
                label={label}
              >
                {this.renderTree(element.children)}
              </ListElement>
            );
          })}
        </List>
      );
    }
    return null;
  };

  render() {
    return (
      <div
        className="archive-files"
        ref={(c) => {
          this.container = c;
        }}
      >
        <a onClick={this.toggleFiles}>
          <span>view files &nbsp;</span>
          <i className="fitted icon dropdown" />
        </a>
        <Detached
          zIndex={99999}
          isOpen={this.state.open}
          positionAt="left bottom"
          positionTarget={this.container}
        >
          <ClickOut
            onClickOut={() => {
              this.setState({ open: false });
            }}
          >
            <div className={classNames('files', { hidden: !this.state.open })}>
              {this.getFileList()}
            </div>
          </ClickOut>
        </Detached>
      </div>
    );
  }
}
