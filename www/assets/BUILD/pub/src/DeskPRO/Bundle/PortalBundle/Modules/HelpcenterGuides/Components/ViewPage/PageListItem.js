import PropTypes from 'prop-types';
import React from 'react';
import Link from 'react-router/lib/Link';
import Highlighter from 'react-highlight-words';
import classNames from 'classnames';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';

class PageListItem extends React.Component {
  static propTypes = {
    page:            PropTypes.object,
    pageList:        PropTypes.array,
    guideSlug:       PropTypes.string,
    pageSlug:        PropTypes.string,
    path:            PropTypes.string,
    filter:          PropTypes.string,
    delta:           PropTypes.number,
    expanded:        PropTypes.bool,
    expandedList:    PropTypes.object,
    grabPageFromApi: PropTypes.func,
    filterPage:      PropTypes.func,
    togglePage:      PropTypes.func,
  };

  static defaultProps = {
    expandable: true,
  };

  getLevelPrefix = (delta = 0) => {
    const { page, pageList } = this.props;
    const depthPage = pageList.find(p => p.id === page.id);
    switch (depthPage.depth + delta) {
      case 0:
        return '';
      case 1:
        return 'sup';
      case 2:
        return 'sub';
      case 3:
        return 'under';
      default:
        return 'under';
    }
  };

  handleClick = (e) => {
    const { page, expanded } = this.props;
    if (['path', 'svg', 'FIGURE'].indexOf(e.target.tagName) !== -1) {
      this.props.togglePage(e, page, expanded);
    } else {
      this.props.grabPageFromApi(page.slug);
    }
  };

  isExpanded = () => {
    const { expandedList, page, expanded } = this.props;
    if (typeof expandedList[page.id] !== 'undefined') {
      return expandedList[page.id];
    }
    return expanded;
  }

  renderChildren = () => {
    const { page, pageList, guideSlug, pageSlug, expandedList, filter, filterPage, togglePage, grabPageFromApi, delta } = this.props;
    if (!Object.values(page.children).length) {
      return null;
    }
    const prefix = this.getLevelPrefix(delta);
    const style = {};
    if (!this.isExpanded()) {
      style.display = 'none';
    }
    return (
      <ul
        className={classNames(`dp-po-guides-search-content-${prefix}list`)}
        style={style}
      >
        {Object.values(page.children)
          .filter(t => filterPage(t))
          .sort((a, b) => parseInt(a.display_order, 10) - parseInt(b.display_order, 10))
          .map(child => (
            <PageListItem
              key={child.slug}
              page={child}
              pageList={pageList}
              delta={delta}
              guideSlug={guideSlug}
              pageSlug={pageSlug}
              path={this.props.path}
              grabPageFromApi={grabPageFromApi}
              filter={filter}
              filterPage={filterPage}
              togglePage={togglePage}
              expanded={(filter !== '' || child.slug === pageSlug || typeof Object.values(child.children)
                .find(c => c.slug === pageSlug ||  Object.values(c.children).find(cc => cc.slug === pageSlug)) !== 'undefined')}
              expandedList={expandedList}
            />
            )
          )}
      </ul>
    );
  };

  render() {
    const { page, pageSlug, guideSlug, togglePage, expanded, filter, delta } = this.props;

    let baseUrl = window.DESKPRO_BASE_URL;
    if (baseUrl) {
      baseUrl = baseUrl.replace(/\/+$/, '');
    }

    const prefix = this.getLevelPrefix(delta);
    if (page.no_content === '1') {
      return (
        <li className={`dp-po-guides-search-content-${prefix}item`} key={page.slug}>
          <div
            className={classNames(`dp-po-guides-search-content-${prefix}link chapter`, { expanded: this.isExpanded() })}
            onClick={e => togglePage(e, page, expanded)}
          >
            <Highlighter
              highlightClassName="filter-highlight"
              className="dp-po-guide-topic-list-item"
              searchWords={[filter]}
              textToHighlight={page.title}
            />
          </div>
          {this.renderChildren()}
        </li>
      );
    }
    return (
      <li className={`dp-po-guides-search-content-${prefix}item`} key={page.slug}>
        <Link
          className={classNames(`dp-po-guides-search-content-${prefix}link`, { expanded: this.isExpanded(), active: page.slug === pageSlug })}
          to={`${baseUrl}/guides/${guideSlug}/${page.slug}`}
          activeClassName="active"
          onClick={this.handleClick}
        >
          <Highlighter
            highlightClassName="filter-highlight"
            className="dp-po-guide-topic-list-item"
            searchWords={[filter]}
            textToHighlight={page.title}
          />
          {Object.values(page.children).length > 0 && <figure className="dp-po-icon"><FontAwesomeIcon icon={['fas', 'caret-down']} /></figure>}
        </Link>
        {this.renderChildren()}
      </li>
    );
  }
}
export default PageListItem;
