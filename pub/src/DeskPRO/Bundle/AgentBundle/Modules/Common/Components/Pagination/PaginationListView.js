import React, {Component, PropTypes} from 'react';
import createFragment from 'react-addons-create-fragment';
import {PageView} from './PageView';

export class PaginationListView extends Component {

  static propTypes = {
    pageNum: PropTypes.number.isRequired,
    pageRangeDisplayed: PropTypes.number.isRequired
  };

  render() {
    const {pageNum, pageRangeDisplayed } = this.props;
    const items = {};

    if (pageNum <= pageRangeDisplayed) {
      for (let index = 0; index < this.props.pageNum; index++) {
        items['key' + index] = (<PageView
          onClick={this.props.onPageSelected.bind(null, index)}
          selected={this.props.selected === index}
          pageClassName={this.props.pageClassName}
          pageLinkClassName={this.props.pageLinkClassName}
          activeClassName={this.props.activeClassName}
          page={index + 1}/>)
      }
    } else {
      let leftSide = (this.props.pageRangeDisplayed / 2);
      let rightSide = (this.props.pageRangeDisplayed - leftSide);

      if (this.props.selected > this.props.pageNum - this.props.pageRangeDisplayed / 2) {
        rightSide = this.props.pageNum - this.props.selected;
        leftSide = this.props.pageRangeDisplayed - rightSide;
      } else if (this.props.selected < this.props.pageRangeDisplayed / 2) {
        leftSide = this.props.selected;
        rightSide = this.props.pageRangeDisplayed - leftSide;
      }

      let index;
      let page;

      for (index = 0; index < this.props.pageNum; index++) {

        page = index + 1;

        const pageView = (
          <PageView
            onClick={this.props.onPageSelected.bind(null, index)}
            selected={this.props.selected === index}
            pageClassName={this.props.pageClassName}
            pageLinkClassName={this.props.pageLinkClassName}
            activeClassName={this.props.activeClassName}
            page={index + 1}/>
        );

        if (page <= this.props.marginPagesDisplayed) {
          items['key' + index] = pageView;
          continue;
        }

        if (page > this.props.pageNum - this.props.marginPagesDisplayed) {
          items['key' + index] = pageView;
          continue;
        }

        if ((index >= this.props.selected - leftSide) && (index <= this.props.selected + rightSide)) {
          items['key' + index] = pageView;
          continue;
        }

        let keys = Object.keys(items);
        let breakLabelKey = keys[keys.length - 1];
        let breakLabelValue = items[breakLabelKey];

        if (breakLabelValue !== this.props.breakLabel) {
          items['key' + index] = this.props.breakLabel;
        }
      }
    }

    return (
      <ul className={this.props.subContainerClassName}>
        {createFragment(items)}
      </ul>
    );
    /*
     var items = {};

     if (this.props.pageNum <= this.props.pageRangeDisplayed) {

     for (var index = 0; index < this.props.pageNum; index++) {
     items['key' + index] = <PageView
     onClick={this.props.onPageSelected.bind(null, index)}
     selected={this.props.selected === index}
     pageClassName={this.props.pageClassName}
     pageLinkClassName={this.props.pageLinkClassName}
     activeClassName={this.props.activeClassName}
     page={index + 1}/>
     }

     } else {

     var leftSide = (this.props.pageRangeDisplayed / 2);
     var rightSide = (this.props.pageRangeDisplayed - leftSide);

     if (this.props.selected > this.props.pageNum - this.props.pageRangeDisplayed / 2) {
     rightSide = this.props.pageNum - this.props.selected;
     leftSide = this.props.pageRangeDisplayed - rightSide;
     }
     else if (this.props.selected < this.props.pageRangeDisplayed / 2) {
     leftSide = this.props.selected;
     rightSide = this.props.pageRangeDisplayed - leftSide;
     }

     var index;
     var page;

     for (index = 0; index < this.props.pageNum; index++) {

     page = index + 1;

     var pageView = (
     <PageView
     onClick={this.props.onPageSelected.bind(null, index)}
     selected={this.props.selected}
     dropdown={(this.props.selected === index) && this.props.dropdown}
     pageClassName={this.props.pageClassName}
     pageLinkClassName={this.props.pageLinkClassName}
     activeClassName={this.props.activeClassName}
     active={this.props.selected === index}
     page={index + 1}
     pageNum={this.props.pageNum}
     onPageSelected={this.props.onPageSelected}/>
     );

     if (page <= this.props.marginPagesDisplayed) {
     items['key' + index] = pageView;
     continue;
     }

     if (page > this.props.pageNum - this.props.marginPagesDisplayed) {
     items['key' + index] = pageView;
     continue;
     }

     if ((index >= this.props.selected - leftSide) && (index <= this.props.selected + rightSide)) {
     items['key' + index] = pageView;
     continue;
     }

     var keys = Object.keys(items);
     var breakLabelKey = keys[keys.length - 1];
     var breakLabelValue = items[breakLabelKey];

     if (breakLabelValue !== this.props.breakLabel) {
     items['key' + index] = this.props.breakLabel;
     }
     }
     }

     return (
     <ul className={this.props.subContainerClassName}>
     { createFragment(items) }
     </ul>
     );*/
  }
}
