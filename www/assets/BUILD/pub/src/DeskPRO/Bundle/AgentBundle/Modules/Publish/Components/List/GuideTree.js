import PropTypes from 'prop-types';
import React from 'react';
import { connect } from 'react-redux';
import SortableTree, { toggleExpandedForAll } from 'react-sortable-tree';
import classNames from 'classnames';
import Renderer from 'DeskPRO/Component/Tree/Renderer';
import * as actions from '../../Actions/guideListActions';
import { treeSelector } from '../../Selectors/guide';

class TopicRenderer extends React.Component {
  static propTypes = {
    node:      PropTypes.object.isRequired,
    className: PropTypes.string,
  };

  render() {
    const { node, className, ...props } = this.props;
    return (
      <Renderer
        node={node}
        className={classNames(className, node.status, node.hidden_status)}
        {...props}
      />
    );
  }
}

@connect(state => ({
  tree: treeSelector(state)
}))
export class GuideTreeContainer extends React.Component {
  static propTypes = {
    guideId:         PropTypes.number,
    height:          PropTypes.number,
    tree:            PropTypes.object,
    openTopic:       PropTypes.func,
    dispatch:        PropTypes.func.isRequired,
    displayStatuses: PropTypes.arrayOf(PropTypes.oneOf(['draft', 'unpublished', 'archived'])),
    canDrag:         PropTypes.bool
  };

  static defaultProps = {
    displayStatuses: []
  };

  constructor(props) {
    super(props);
    this.reloadTree();
  }

  handleChange = (treeData) => {
    this.props.dispatch(actions.saveTree(this.props.guideId, treeData));
  };

  reloadTree = () => {
    this.props.dispatch(actions.loadTree(this.props.guideId));
  };

  render() {
    let tree = [];
    if (this.props.tree.size && this.props.tree.get('id') === this.props.guideId) {
      tree = this.props.tree.get('tree').toJS();
    }
    return (
      <GuideTree
        tree={tree}
        height={this.props.height}
        handleChange={this.handleChange}
        onClick={node => this.props.openTopic(node.id)}
        reloadTree={this.reloadTree}
        displayStatuses={this.props.displayStatuses}
        canDrag={this.props.canDrag}
      />
    );
  }
}
export class GuideTree extends React.Component {
  static propTypes = {
    tree:            PropTypes.array,
    height:          PropTypes.number,
    onClick:         PropTypes.func,
    handleChange:    PropTypes.func,
    reloadTree:      PropTypes.func,
    displayStatuses: PropTypes.array,
    canDrag:         PropTypes.bool
  };
  static defaultProps = {
    height: 800,
    onClick() {},
    handleChange() {},
    reloadTree() {}
  };

  constructor(props) {
    super(props);
    this.state = {
      active:   null,
      treeData: this.filterTree(this.props.tree),
    };
  }

  componentWillMount = () => {
    window.document.addEventListener('dpSelectTopic', (e) => {
      if (e.detail.id) {
        this.setState({
          active: e.detail.id
        });
      }
    });
    window.document.addEventListener('dpGuideReloadTree', () => {
      this.props.reloadTree();
    });
  };

  componentWillReceiveProps(nextProps) {
    const expanded = true;
    this.setState({
      treeData: toggleExpandedForAll({
        treeData: this.filterTree(nextProps.tree),
        expanded,
      }),
    });
  }

  onClick = (node) => {
    if (parseInt(node.id, 10) === this.state.active) {
      this.setState({
        active: null
      });
    } else {
      this.setState({
        active: parseInt(node.id, 10)
      });
    }
    this.props.onClick(node);
  };

  onMoveNode = (topic) => {
    if (topic.path.length === 1) {
      alert('Topic at root have no content, the topic will keep its content but you won\'t be able to edit it');
    }
    const params = { detail: { root: topic.path.length === 1 } };
    window.document.dispatchEvent(new CustomEvent(`dpMoveTopic${topic.node.id}`, params));
  };

  filterTree = tree => tree.filter((element) => {
    switch (element.status) {
      case 'hidden':
        return this.props.displayStatuses.indexOf(element.hidden_status) !== -1;
      case 'archived':
        return this.props.displayStatuses.indexOf('archived') !== -1;
      default:
        return true;
    }
  });

  handleChange = (treeData) => {
    this.setState({
      treeData
    });
    this.props.handleChange(treeData);
  };

  generateNodeProps = (rowInfo) => {
    let id = rowInfo.node.id;
    if (typeof id === 'string') {
      id = window.parseInt(id, 10);
    }
    return ({
      onClick:   () => this.onClick(rowInfo.node),
      className: classNames({ active: id === this.state.active })
    });
  };

  render() {
    return (
      <div style={{ height: this.props.height }}>
        <SortableTree
          rowHeight={40}
          scaffoldBlockPxWidth={30}
          treeData={this.state.treeData}
          onChange={this.handleChange}
          onMoveNode={this.onMoveNode}
          nodeContentRenderer={TopicRenderer}
          generateNodeProps={this.generateNodeProps}
          canDrag={this.props.canDrag}
        />
      </div>
    );
  }
}
