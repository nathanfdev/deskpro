'use strict';

var React = require('react');

var PageView = React.createClass({
  render: function() {
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
            <i className="fa fa-caret-down" />
          : ''}
        </a>
        {this.props.dropdown ?
          <div className="pagination-dropdown">
            <ul>
              {
                pages.map((page) => {
                  const className = this.props.selected === page - 1 ? 'active' : '';
                  return <li key={page}>
                    <a href="#" onClick={this.props.onPageSelected.bind(null, page - 1)} className={className}>
                      {page}
                    </a>
                  </li>;
                })
              }
            </ul>
          </div> : ''}
      </li>
    );
  }
});

module.exports = PageView;
