import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { List, ListElement } from 'DeskPRO/Component/Semantic/List';
import * as actions from '../../Actions/archiveActions';

@connect(state => ({
  archive: state.Tickets.archive
}))
export class ArchiveMenuContainer extends React.Component {
  static propTypes = {
    authId:   PropTypes.string,
    title:    PropTypes.string,
    href:     PropTypes.string,
    dispatch: PropTypes.func,
    archive:  PropTypes.object.isRequired
  };

  constructor(props) {
    super(props);
    this.state = {
      file: this.props.archive.get('files')
    };
  }

  componentWillMount() {
    const { dispatch, authId } = this.props;

    dispatch(actions.loadFiles(authId));
  }

  componentWillReceiveProps(nextProps) {
    this.setState({
      file: nextProps.archive.get('files')
    });
  }

  getLink = (element) => {
    const { authId } = this.props;
    return `/api/v2/blobs/${authId}/download/${element.name}`;
  };

  render() {
    return (
      <ArchiveMenu
        title={this.props.title}
        href={this.props.href}
        list={this.props.archive.get('files')}
        getLink={this.getLink}
      />
    );
  }
}

export class ArchiveMenu extends React.Component {
  static propTypes = {
    list:    PropTypes.object,
    getLink: PropTypes.func,
    title:   PropTypes.string,
    href:    PropTypes.string,
  };

  static defaultProps = {
    list: {},
    getLink() {}
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
        element.children = ArchiveMenu.unflattenList(list, localIndex + 1, level + 1);
        tree.push(element);
        localIndex += ArchiveMenu.recursiveLength(element.children);
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
        length += ArchiveMenu.recursiveLength(e.children);
      }
    }
    return length;
  }

  constructor(props) {
    super(props);
    this.state = {
      tree: ArchiveMenu.unflattenList(this.props.list, 0, 0)
    };
  }

  componentWillReceiveProps(nextProps) {
    console.log(nextProps);
    if (nextProps.list !== this.props.list) {
      this.setState({
        tree: ArchiveMenu.unflattenList(nextProps.list, 0, 0)
      });
    }
  }

  getFileList() {
    return this.renderTree(this.state.tree);
  }

  renderTree(tree) {
    if (tree) {
      return (
        <List>
          {tree.map(element =>
            <ListElement
              icon={element.dir ? 'folder' : 'file'}
              href={element.dir ? '' : this.props.getLink(element)}
              label={element.filename}
            >
              {this.renderTree(element.children)}
            </ListElement>
          )}
        </List>
      );
    }
    return null;
  }

  render() {
    return (
      <div className="archive-popup">
        <div className="header"><a href={this.props.href}>{this.props.title}</a></div>
        <div className="content">
          {this.getFileList()}
        </div>
      </div>
    );
  }
}
