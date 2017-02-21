import React, { PropTypes } from 'react';
import SortableTree from 'react-sortable-tree';
import classNames from 'classnames';
import Renderer from 'DeskPRO/Component/Tree/Renderer';

export class ManualTreeContainer extends React.Component {
  static propTypes = {
    tree: PropTypes.array
  };

  render() {
    return (
      <ManualTree
        tree={this.props.tree}
      />
    );
  }
}
export class ManualTree extends React.Component {
  static propTypes = {
    tree:    PropTypes.array,
    onClick: PropTypes.func
  };
  static defaultProps = {
    onClick() {}
  };

  constructor(props) {
    super(props);
    this.state = {
      active:   null,
      treeData: this.props.tree,
    };
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
  };

  generateNodeProps = rowInfo => ({
    onClick:   () => this.onClick(rowInfo.node),
    className: classNames({ active: rowInfo.node === this.state.active })
  });

  render() {
    return (
      <div style={{ height: 800 }}>
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
