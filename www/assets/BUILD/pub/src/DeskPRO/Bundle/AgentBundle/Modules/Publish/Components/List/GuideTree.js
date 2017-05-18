import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import SortableTree, { toggleExpandedForAll } from 'react-sortable-tree';
import classNames from 'classnames';
import Renderer from 'DeskPRO/Component/Tree/Renderer';
import * as actions from '../../Actions/guideListActions';
import { treeSelector } from '../../Selectors/guide';

@connect(state => ({
  tree: treeSelector(state)
}))
export class GuideTreeContainer extends React.Component {
  static propTypes = {
    guideId:   PropTypes.number,
    height:    PropTypes.number,
    tree:      PropTypes.object,
    openTopic: PropTypes.func,
    dispatch:  PropTypes.func.isRequired
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
      />
    );
  }
}
export class GuideTree extends React.Component {
  static propTypes = {
    tree:         PropTypes.array,
    height:       PropTypes.number,
    onClick:      PropTypes.func,
    handleChange: PropTypes.func,
    reloadTree:   PropTypes.func,
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
      treeData: this.props.tree,
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
        treeData: nextProps.tree,
        expanded,
      }),
    });
  }

  onClick = (node) => {
    if (node.id.toInt() === this.state.active) {
      this.setState({
        active: null
      });
    } else {
      this.setState({
        active: node.id.toInt()
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
          nodeContentRenderer={Renderer}
          generateNodeProps={this.generateNodeProps}
        />
      </div>
    );
  }
}
