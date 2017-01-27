import React, { PropTypes } from 'react';
import { List, ListElement } from 'DeskPRO/Component/Semantic/List';

class ArchiveMenu extends React.Component {
  static propTypes = {
    list: PropTypes.array
  };

  static unflattenList(list, index, level) {
    const tree = [];
    let localIndex = index;

    while (localIndex < list.length) {
      const path = list[localIndex].name.split('/');
      list[localIndex].dir = path[path.length - 1] === '';

      if (!list[localIndex].dir) {
        if (level > path.length - 1) {
          return tree;
        }
        list[localIndex].filename = path[path.length - 1];
        tree.push(list[localIndex]);
      } else {
        if (level > path.length - 2) {
          return tree;
        }
        list[localIndex].filename = path[path.length - 2];
        list[localIndex].children = ArchiveMenu.unflattenList(list, localIndex + 1, level + 1);
        tree.push(list[localIndex]);
        localIndex += ArchiveMenu.recursiveLength(list[localIndex].children);
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

  static renderTree(tree) {
    if (tree) {
      return (
        <List>
          {tree.map(element =>
            <ListElement
              icon={element.dir ? 'folder' : 'file'}
              label={element.name}
            >
              {ArchiveMenu.renderTree(element.children)}
            </ListElement>
          )}
        </List>
      );
    }
    return null;
  }

  constructor(props) {
    super(props);
    this.state = {
      tree: ArchiveMenu.unflattenList(this.props.list, 0, 0)
    };
  }

  componentWillReceiveProps(nextProps) {
    if (nextProps.list !== this.props.list) {
      this.setState({
        tree: ArchiveMenu.unflattenList(nextProps.list, 0, 0)
      });
    }
  }

  getFileList() {
    return ArchiveMenu.renderTree(this.state.tree);
  }

  render() {
    return (
      <div>
        {this.getFileList()}
      </div>
    );
  }
}

export default ArchiveMenu;
