import React, {Component, PropTypes} from 'react';

export class PageView extends Component {

  static propTypes = {
    active: PropTypes.bool,
    pageNum: PropTypes.number.isRequired,
    pageRangeDisplayed: PropTypes.number.isRequired,
    marginPagesDisplayed: PropTypes.number.isRequired,
    previousLabel: PropTypes.node,
    nextLabel: PropTypes.node,
    breakLabel: PropTypes.node,
    clickCallback: PropTypes.func,
    initialSelected: PropTypes.number,
    forceSelected: PropTypes.number,
    containerClassName: PropTypes.string,
    subContainerClassName: PropTypes.string,
    pageClassName: PropTypes.string,
    pageLinkClassName: PropTypes.string,
    activeClassName: PropTypes.string,
    previousClassName: PropTypes.string,
    nextClassName: PropTypes.string,
    previousLinkClassName: PropTypes.string,
    nextLinkClassName: PropTypes.string,
    disabledClassName: PropTypes.string
  };

  render() {
    var linkClassName = this.props.pageLinkClassName;
    var cssClassName = this.props.pageClassName;

    if (this.props.active) {
      if (typeof(cssClassName) !== 'undefined') {
        cssClassName = cssClassName + ' ' + this.props.activeClassName;
      } else {
        cssClassName = this.props.activeClassName;
      }
    }

    // Build up an array of page numbers
    let pages = [];
    for (let page = 1; page <= this.props.pageNum; page++) {
      pages.push(page);
    }

    return (
      <li className={cssClassName}>
        <a {...this.props} href="" className={linkClassName}>
          {this.props.page}
          {this.props.active ?
            <i className="fa fa-caret-down"/>
            : ''}
        </a>
        {this.props.dropdown ?
          <div className="pagination-dropdown">
            <ul>
              {
                pages.map((page) => {
                  const className = this.props.selected === page - 1 ? 'active' : '';
                  return (
                    <li key={page}>
                      <a href="#" onClick={this.props.onPageSelected.bind(null, page - 1)} className={className}>
                        {page}
                      </a>
                    </li>
                  );
                })
              }
            </ul>
          </div> : ''}
      </li>
    );
  }
}