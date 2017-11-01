import PropTypes from 'prop-types';
import React, { Component } from 'react';
import createFragment from 'react-addons-create-fragment';
import { PageView } from './PageView';

export class PaginationListView extends Component {

  static propTypes = {
    dropdown:              PropTypes.bool,
    subContainerClassName: PropTypes.string,
    pageClassName:         PropTypes.string,
    pageLinkClassName:     PropTypes.string,
    activeClassName:       PropTypes.string,
    breakLabel:            PropTypes.node,
    currentPage:           PropTypes.number.isRequired,
    pageNum:               PropTypes.number.isRequired,
    pageRangeDisplayed:    PropTypes.number.isRequired,
    marginPagesDisplayed:  PropTypes.number.isRequired,
    onPageSelected:        PropTypes.func.isRequired
  };

  render() {
    const { pageNum, pageRangeDisplayed, marginPagesDisplayed, onPageSelected, currentPage, breakLabel, dropdown } = this.props;
    const { subContainerClassName, pageClassName, pageLinkClassName, activeClassName } = this.props;
    const items = {};

    if (pageNum <= pageRangeDisplayed) {
      for (let index = 0; index < pageNum; index++) {
        items['key' + index] = (
          <PageView onClick={onPageSelected}
            dropdown={dropdown}
            currentPage={currentPage}
            pageNum={pageNum}
            pageClassName={pageClassName}
            pageLinkClassName={pageLinkClassName}
            activeClassName={activeClassName}
            page={index + 1}
          />
        );
      }
    } else {
      let leftSide = (pageRangeDisplayed / 2);
      let rightSide = (pageRangeDisplayed - leftSide);

      if (currentPage > pageNum - pageRangeDisplayed / 2) {
        rightSide = pageNum - currentPage;
        leftSide = pageRangeDisplayed - rightSide;
      } else if (currentPage < pageRangeDisplayed / 2) {
        leftSide = currentPage;
        rightSide = pageRangeDisplayed - leftSide;
      }

      let index;
      let page;

      for (index = 0; index < pageNum; index++) {
        page = index + 1;

        const pageView = (
          <PageView onClick={onPageSelected}
            dropdown={dropdown}
            currentPage={currentPage}
            pageClassName={pageClassName}
            pageLinkClassName={pageLinkClassName}
            activeClassName={activeClassName}
            pageNum={pageNum}
            page={index + 1}
          />
        );

        if (page <= marginPagesDisplayed) {
          items['key' + index] = pageView;
          continue;
        }

        if (page > pageNum - marginPagesDisplayed) {
          items['key' + index] = pageView;
          continue;
        }

        if ((index >= currentPage - leftSide) && (index <= currentPage + rightSide)) {
          items['key' + index] = pageView;
          continue;
        }

        const keys = Object.keys(items);
        const breakLabelKey = keys[keys.length - 1];
        const breakLabelValue = items[breakLabelKey];

        if (breakLabelValue !== breakLabel) {
          items['key' + index] = breakLabel;
        }
      }
    }

    return (
      <ul className={subContainerClassName}>
        {createFragment(items)}
      </ul>
    );
  }
}
