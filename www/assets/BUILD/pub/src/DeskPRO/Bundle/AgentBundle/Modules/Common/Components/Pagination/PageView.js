import PropTypes from 'prop-types';
import React, { Component } from 'react';

export class PageView extends Component {

  static propTypes = {
    dropdown:        PropTypes.bool,
    currentPage:     PropTypes.number.isRequired,
    page:            PropTypes.number.isRequired,
    pageNum:         PropTypes.number.isRequired,
    onClick:         PropTypes.func,
    activeClassName: PropTypes.string
  };

  renderCaret(active) {
    if (active) {
      return (<i className="fa fa-caret-down" />);
    }
  }

  renderDropdownOption(page) {
    const { currentPage, onClick } = this.props;
    const className = currentPage === page ? 'active' : '';
    return (
      <li key={page}>
        <a href="#" onClick={onClick.bind(null, page - 1)} className={className}>
          {page}
        </a>
      </li>
    );
  }

  renderDropDown(active) {
    const { pageNum, dropdown } = this.props;

    if (active && dropdown) {
      // Build up an array of page numbers
      const pages = [];
      for (let value = 1; value <= pageNum; value++) {
        pages.push(value);
      }

      return (
        <div className="pagination-dropdown">
          <ul>
            {
              pages.map((page) => this.renderDropdownOption(page))
            }
          </ul>
        </div>
      );
    }
  }

  render() {
    const { activeClassName, currentPage, page, onClick } = this.props;
    const active = currentPage === page;

    return (
      <li className={active ? activeClassName : false}>
        <a {...this.props} href="" className={active ? activeClassName : false} onClick={onClick.bind(null, page - 1)}>
          {page}
          {this.renderCaret(active)}
        </a>
        {this.renderDropDown(active)}
      </li>
    );
  }
}
