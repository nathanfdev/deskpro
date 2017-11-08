import PropTypes from 'prop-types';
import React, { Component } from 'react';
import classNames from 'classnames';
import { PaginationListView } from './PaginationListView';

export class PaginationBoxView extends Component {

  static propTypes = {
    currentPage:           PropTypes.number.isRequired,
    pageNum:               PropTypes.number.isRequired,
    pageRangeDisplayed:    PropTypes.number.isRequired,
    marginPagesDisplayed:  PropTypes.number.isRequired,
    previousLabel:         PropTypes.node,
    nextLabel:             PropTypes.node,
    breakLabel:            PropTypes.node,
    clickCallback:         PropTypes.func,
    forceSelected:         PropTypes.number,
    containerClassName:    PropTypes.string,
    subContainerClassName: PropTypes.string,
    pageClassName:         PropTypes.string,
    pageLinkClassName:     PropTypes.string,
    activeClassName:       PropTypes.string,
    previousClassName:     PropTypes.string,
    nextClassName:         PropTypes.string,
    previousLinkClassName: PropTypes.string,
    nextLinkClassName:     PropTypes.string,
    disabledClassName:     PropTypes.string
  };

  static defaultProps = {
    currentPage:           1,
    pageRangeDisplayed:    2,
    marginPagesDisplayed:  3,
    previousClassName:     'previous',
    nextClassName:         'next',
    previousLabel:         'Previous',
    nextLabel:             'Next',
    breakLabel:            '...',
    disabledClassName:     'disabled',
    containerClassName:    'pages-list',
    subContainerClassName: 'pages-list',
    activeClassName:       'current-page',

  };

  constructor(props) {
    super(props);
    this.state = { dropdown: false };
  }

  componentWillReceiveProps(nextProps) {
    if (typeof nextProps.forceSelected !== 'undefined' && nextProps.forceSelected !== this.props.currentPage) {
      this.setState({ selected: nextProps.forceSelected });
    }
  }

  handlePageSelected(selected, event) {
    event.stopPropagation();
    event.preventDefault();
    const { currentPage, clickCallback } = this.props;

    if (currentPage === selected + 1) {
      // Toggle the dropdown list
      this.setState({ dropdown: !this.state.dropdown });
      return;
    }

    this.setState({
      dropdown: false
    });

    if (typeof(clickCallback) !== 'undefined' && typeof(clickCallback) === 'function') {
      clickCallback(selected + 1);
    }
  }

  handlePreviousPage(event) {
    event.preventDefault();
    if (this.props.currentPage > 1) {
      this.handlePageSelected(this.props.currentPage - 2, event);
    }
  }

  handleNextPage(event) {
    event.preventDefault();
    if (this.props.currentPage < this.props.pageNum) {
      this.handlePageSelected(this.props.currentPage, event);
    }
  }

  render() {
    const { disabledClassName, previousClassName, nextClassName, containerClassName, subContainerClassName, pageClassName, pageLinkClassName, activeClassName } = this.props;
    const { currentPage, pageNum, pageRangeDisplayed, marginPagesDisplayed, breakLabel } = this.props;
    const previousClasses = classNames(previousClassName, { [disabledClassName]: this.props.currentPage === 1 });
    const nextClasses = classNames(nextClassName, { [disabledClassName]: this.props.currentPage === this.props.pageNum });

    return (
      <div className="dpw--pagination">
        <ul className={containerClassName}>
          <li onClick={this.handlePreviousPage.bind(this)} className={previousClasses}>
            <a href=""><i className="fa fa-caret-left"></i></a>
          </li>
          <li>
            <hr />
          </li>
          <PaginationListView onPageSelected={this.handlePageSelected.bind(this)}
            currentPage={currentPage}
            pageNum={pageNum}
            pageRangeDisplayed={pageRangeDisplayed}
            marginPagesDisplayed={marginPagesDisplayed}
            breakLabel={breakLabel}
            subContainerClassName={subContainerClassName}
            pageClassName={pageClassName}
            pageLinkClassName={pageLinkClassName}
            activeClassName={activeClassName}
            disabledClassName={disabledClassName}
            dropdown={this.state.dropdown}
          />
          <li>
            <hr />
          </li>
          <li onClick={this.handleNextPage.bind(this)} className={nextClasses}>
            <a href=""><i className="fa fa-caret-right"></i></a>
          </li>
        </ul>
      </div>
    );
  }

}
