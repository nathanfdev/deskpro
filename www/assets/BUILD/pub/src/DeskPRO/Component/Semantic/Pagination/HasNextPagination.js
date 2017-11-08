import PropTypes from 'prop-types';
import React from 'react';
import classNames from 'classnames';

class HasNextPagination extends React.Component {

  static propTypes = {
    pageNum: PropTypes.number,
    hasNext: PropTypes.bool,
    onClick: PropTypes.func
  };

  onClickPrev = (event) => {
    event.preventDefault();
    const { pageNum, onClick } = this.props;

    if (pageNum > 1) {
      onClick(pageNum - 1);
    }
  };

  onClickNext = (event) => {
    event.preventDefault();
    const { hasNext, pageNum, onClick } = this.props;

    if (hasNext) {
      onClick(pageNum + 1);
    }
  };

  render() {
    const { pageNum = 1, hasNext } = this.props;
    const hasPrev = pageNum > 1;

    return (
      <div className="ui borderless pagination menu">
        {hasPrev &&
          <a className="item" onClick={this.onClickPrev}>
            <i className="left arrow icon" />
          </a>}
        <span className="item">{pageNum}</span>
        <a className={classNames('item', { disabled: !hasNext })} onClick={this.onClickNext}>
          <i className="icon right arrow" />
        </a>
      </div>
    );
  }
}

export default HasNextPagination;
