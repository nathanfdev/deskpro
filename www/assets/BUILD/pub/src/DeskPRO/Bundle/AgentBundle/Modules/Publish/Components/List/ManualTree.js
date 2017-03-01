import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import SortableTree from 'react-sortable-tree';
import classNames from 'classnames';
import Renderer from 'DeskPRO/Component/Tree/Renderer';
import * as actions from '../../Actions/manualListActions';
import { treeSelector } from '../../Selectors/manual';

@connect(state => ({
  trees: treeSelector(state)
}))
export class ManualTreeContainer extends React.Component {
  static propTypes = {
    manualId:  PropTypes.number,
    height:    PropTypes.number,
    trees:     PropTypes.object,
    openTopic: PropTypes.func,
    dispatch:  PropTypes.func.isRequired
  };

  constructor(props) {
    super(props);
    this.props.dispatch(actions.loadTree(this.props.manualId));
  }

  handleChange = (treeData) => {
    this.props.dispatch(actions.saveTree(this.props.manualId, treeData));
  };

  render() {
    const trees = this.props.trees.filter(x => x.get('id') === this.props.manualId);
    let tree = [];
    if (trees.size) {
      tree = trees.first().get('tree').toJS();
    }
    return (
      <ManualTree
        tree={tree}
        height={this.props.height}
        handleChange={this.handleChange}
        onClick={node => this.props.openTopic(node.id)}
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
  };
  static defaultProps = {
    height: 800,
    onClick() {},
    handleChange() {}
  };

  constructor(props) {
    super(props);
    this.state = {
      active:   null,
      treeData: this.props.tree,
    };
  }

  componentWillReceiveProps(nextProps) {
    this.setState({
      treeData: nextProps.tree
    });
  }

  onClick = (node) => {
    this.setState({
      active: node
    });
    this.props.onClick(node);
  };

  handleChange = (treeData) => {
    this.setState({
      treeData
    });
    this.props.handleChange(treeData);
  };

  generateNodeProps = rowInfo => ({
    onClick:   () => this.onClick(rowInfo.node),
    className: classNames({ active: rowInfo.node === this.state.active })
  });

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
