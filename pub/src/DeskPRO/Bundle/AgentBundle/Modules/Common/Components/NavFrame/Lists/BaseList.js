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

    const onClick = event => {
      event.preventDefault();
      onItemControlClick(event);
    };

    return (
      <a href="" className="list-counter-dropdown active" onClick={onClick}>
        <span>&nbsp;</span>
        <i className="fa fa-angle-down"></i>
      </a>
    );
  }

  renderEditButton() {
    const onClick = event => {
      event.preventDefault();
      this.props.onEdit(event);
    };

    return (
      <div className="list-counter-bucket">
        <a href="#" className="edit-icon" onClick={onClick}>
          <i className="fa fa-cog" />
        </a>
      </div>
    );
  }
}
