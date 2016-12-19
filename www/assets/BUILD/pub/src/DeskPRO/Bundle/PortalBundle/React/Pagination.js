import React, { PropTypes } from 'react';
import _ from 'lodash';

class PaginationLink extends React.Component {

  static propTypes = {
    onClick: PropTypes.func,
    page:    PropTypes.number,
    text:    PropTypes.string
  };

  onClick() {
    const { onClick, page } = this.props;
    onClick(page);
  }

  render() {
    const { text } = this.props;
    return (
      <li>
        <a onClick={this.onClick}>{text}</a>
      </li>
    );
  }
}

export class Pagination extends React.Component {

  render() {
    const current = _.parseInt(this.props.currentPage) || 1;
    const total = _.parseInt(this.props.totalResults) || 0;
    const perPage = _.parseInt(this.props.perPageResults) || 0;
    const pages = Math.ceil(total / perPage);

    if (_.isNaN(pages) || pages < 2) {
      // don't show there's nothing to page
      return null;
    }

    let nextLink = (<li className="inactive"><a href="#">Next</a></li>);
    if (current < pages) {
      nextLink = (<PaginationLink onClick={this.props.pageClick} page={current + 1} text="Next" />);
    }

    let prevLink = (<li className="inactive"><a href="#">Back</a></li>);
    if (current > 1) {
      prevLink = (<PaginationLink onClick={this.props.pageClick} page={current - 1} text="Back" />);
    }

    let prevPaddings = [];
    if (current > 1) {
      prevPaddings.push((<PaginationLink key={'prev' + (current - 1)} onClick={this.props.pageClick} page={current - 1}
        text={current - 1}
      />));
      if (current > 2) {
        prevPaddings.unshift((<PaginationLink key={'prev' + (current - 2)} onClick={this.props.pageClick} page={current - 2}
          text={current - 2}
        />));
      }
    }

    let nextPaddings = [];
    if (current < pages) {
      nextPaddings.push((<PaginationLink key={'next' + (current + 1)} onClick={this.props.pageClick} page={current + 1}
        text={current + 1}
      />));
      if ((current + 1) < pages) {
        nextPaddings.push((<PaginationLink key={'next' + (current + 2)} onClick={this.props.pageClick} page={current + 2}
          text={current + 2}
        />));
      }
    }

    return (
      <ul className="pagination">
        {prevLink}
        {prevPaddings}
        <li className="active-page"><a href="#">{current}</a></li>
        {nextPaddings}
        {nextLink}
      </ul>
    );
  }
}
