import React from 'react';

export class BaseList extends React.Component {

  renderCount(count) {
    if (count >= 0) {
      return (
        <div className="list-counter-bucket">
          {this.renderItemControl()}
          <a className="list-counter active" href="#">{count}</a>
        </div>
      );
    }
  }

  renderItemControl() {
    const { onItemControlClick } = this.props;

    if (!onItemControlClick) {
      return '';
    }

    const onClick = (e) => {
      e.preventDefault();
      onItemControlClick(e);
    };

    return (
      <a href="" className="list-counter-dropdown active" onClick={onClick}>
        <span>&nbsp;</span>
        <i className="fa fa-angle-down"></i>
      </a>
    );
  }
}
