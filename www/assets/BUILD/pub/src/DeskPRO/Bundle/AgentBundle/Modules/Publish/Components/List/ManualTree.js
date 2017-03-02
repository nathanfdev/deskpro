import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import SortableTree from 'react-sortable-tree';
import classNames from 'classnames';
import Renderer from 'DeskPRO/Component/Tree/Renderer';
import * as actions from '../../Actions/manualListActions';
import { treeSelector } from '../../Selectors/manual';

@connect(state => ({
  tree: treeSelector(state)
}))
export class ManualTreeContainer extends React.Component {
  static propTypes = {
    manualId:  PropTypes.number,
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
    this.props.dispatch(actions.saveTree(this.props.manualId, treeData));
  };

  reloadTree = () => {
    this.props.dispatch(actions.loadTree(this.props.manualId));
  };

  render() {
    let tree = [];
    if (this.props.tree.size && this.props.tree.get('id') === this.props.manualId) {
      tree = this.props.tree.get('tree').toJS();
    }
    return (
      <ManualTree
        tree={tree}
        height={this.props.height}
        handleChange={this.handleChange}
        onClick={node => this.props.openTopic(node.id)}
        reloadTree={this.reloadTree}
      />
    );
  }
}
export class ManualTree extends React.Component {
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
    window.document.addEventListener('dpSelectManualTopic', (e) => {
      if (e.detail.id) {
        this.setState({
          active: e.detail.id
        });
      }
    });
    window.document.addEventListener('dpManualReloadTree', () => {
      this.props.reloadTree();
    });
  };

  componentWillReceiveProps(nextProps) {
    this.setState({
      treeData: nextProps.tree
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
          treeData={this.state.treeData}
          onChange={this.handleChange}
          nodeContentRenderer={Renderer}
          generateNodeProps={this.generateNodeProps}
        />
      </div>
    );
  }
}
