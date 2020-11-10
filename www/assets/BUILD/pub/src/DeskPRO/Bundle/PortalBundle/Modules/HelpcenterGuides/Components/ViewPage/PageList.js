import PropTypes from 'prop-types';
import React from 'react';
import { FormattedMessage } from 'react-intl';
import classNames from 'classnames';
import Isvg from 'react-inlinesvg';
import { Scrollbars } from 'react-custom-scrollbars';
import Highlighter from 'react-highlight-words';
import guideDefault from '@deskpro/portal-style/dist/img/page-icons/guide-default.svg';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { IconRenderer } from 'DeskPRO/Component/IconRenderer';
import PageListItem from './PageListItem';

class PageList extends React.Component {
  static propTypes = {
    pages:           PropTypes.array,
    guideSlug:       PropTypes.string,
    guide:           PropTypes.object,
    pageSlug:        PropTypes.string,
    grabPageFromApi: PropTypes.func,
    fixed:           PropTypes.bool,
    sizes:           PropTypes.object,
    toggleMenu:      PropTypes.func,
    twoLevelSection: PropTypes.bool,
  };

  static contextTypes = {
    router: PropTypes.object.isRequired
  };

  static renderNoResults() {
    return (
      <div className="dp-po-guides-search-no-results">
        <FontAwesomeIcon icon={['far', 'search']} className="dp-po-icon" />
        <span><FormattedMessage id="helpcenter.guides.no_matching_pages" /></span>
      </div>
    );
  }

  constructor(props) {
    super(props);
    this.state = {
      path:     '',
      filter:   '',
      expanded: {},
    };
  }

  componentDidMount() {
    this.removeListener = this.context.router.listen(this.locationHasChanged);
  }

  componentWillUnmount() {
    this.removeListener();
  }

  filterPage = (page) => {
    let { filter } = this.state;

    filter = filter.toLowerCase();
    return filter === '' || page.title.toLowerCase().match(filter) || Object.values(page.children).find(c => this.filterPage(c));
  };

  isExpandedPage = (page, recursive = false) => {
    const { pageSlug } = this.props;
    const { expanded, filter } = this.state;
    if (filter) {
      return Object.values(page.children).find(c => this.filterPage(c));
    }
    if (page.slug === pageSlug || Object.values(page.children).find(c => this.isExpandedPage(c, true))) {
      return true;
    }
    if (recursive) {
      return false;
    }
    if (typeof expanded[page.id] !== 'undefined') {
      return expanded[page.id];
    }
    return false;
  };

  grabPageFromApi = (slug) => {
    this.setState({
      filter: ''
    });
    this.props.grabPageFromApi(slug);
  }

  togglePage = (e, page, initial) => {
    e.preventDefault();
    e.stopPropagation();
    this.setState({
      filter: ''
    });
    const { expanded } = this.state;
    if (typeof expanded[page.id] !== 'undefined') {
      expanded[page.id] = !expanded[page.id];
    } else {
      expanded[page.id] = !initial;
    }
    this.setState({
      expanded
    });
  }

  handleFilterChange = (e) => {
    this.setState({
      filter:   e.target.value,
      expanded: {},
    });
  };

  locationHasChanged = (e) => {
    this.setState({
      path: e.pathname
    });
  };

  renderPages(pages, depth = 0, collapse = false, delta = 0) {
    const { guideSlug, pageSlug, pages: pageList } = this.props;
    const { filter, expanded } = this.state;
    const unfilteredPages = pages.filter(t => depth > 0 || t.depth === depth);
    const renderedPages = unfilteredPages
      .filter(t => this.filterPage(t))
      .sort((a, b) => parseInt(a.display_order, 10) - parseInt(b.display_order, 10))
      .map(page => (
        <PageListItem
          key={page.slug}
          page={page}
          pageList={pageList}
          guideSlug={guideSlug}
          pageSlug={pageSlug}
          path={this.state.path}
          grabPageFromApi={this.grabPageFromApi}
          filter={filter}
          delta={delta}
          filterPage={this.filterPage}
          togglePage={this.togglePage}
          expanded={!!(this.isExpandedPage(page))}
          expandedList={expanded}
        />
        )
      );
    if (renderedPages.length === 0 && unfilteredPages.length > 0) {
      return PageList.renderNoResults();
    }
    return (
      <ul className={classNames('dp-po-guides-search-content-list', { collapse })}>
        {renderedPages}
      </ul>
    );
  }

  renderList() {
    const { guide, pages, twoLevelSection } = this.props;
    const { filter } = this.state;
    if (twoLevelSection) {
      const renderedPages = pages
        .filter(t => t.depth === 0)
        .filter(t => this.filterPage(t))
        .sort((a, b) => parseInt(a.display_order, 10) - parseInt(b.display_order, 10))
        .map((page) => {
          const collapsed = !this.isExpandedPage(page);
          return (
            <div className={classNames('dp-po-guides-search-content-accordion', { collapsed })} key={page.slug}>
              <div
                className={classNames('dp-po-guides-search-content-title', { collapsed })}
                onClick={e => this.togglePage(e, page, this.isExpandedPage(page))}
              >
                <IconRenderer
                  object={guide}
                  key={guide.icon_property ? guide.icon_property.urn_path : 'fa-user-headset'}
                  figureStyle={{ backgroundColor: guide.color ? `#${guide.color}` : 'var(--warning)' }}
                  figureClassName="guide-icon"
                  default={<Isvg src={guideDefault} />}
                />
                <Highlighter
                  highlightClassName="filter-highlight"
                  className="title"
                  searchWords={[filter]}
                  textToHighlight={page.title}
                />
                <FontAwesomeIcon icon={['far', 'angle-down']} className="dp-po-icon" />
              </div>
              {this.renderPages(Object.values(page.children), 1, collapsed)}
            </div>
          );
        }
        );
      if (renderedPages.length === 0) {
        return PageList.renderNoResults();
      }
      return (
        <Scrollbars>
          <div className="dp-po-guides-search-content accordion">
            {renderedPages}
          </div>
        </Scrollbars>
      );
    }
    return (
      <Scrollbars>
        <div className="dp-po-guides-search-main">
          {this.renderPages(pages, 0, false, 1)}
        </div>
      </Scrollbars>
    );
  }

  render() {
    const { sizes, toggleMenu, fixed } = this.props;
    const { filter } = this.state;
    const style = {};
    if (sizes && fixed) {
      style.width = sizes.searchWidth;
    }
    return (
      <div className="dp-po-guides-search" style={style}>
        <a onClick={toggleMenu} className="d-block d-md-none close-menu"><FontAwesomeIcon icon={['fal', 'times']} className="dp-po-icon" /></a>
        <form className="dp-po-guides-search-form">
          <input type="search" value={filter} placeholder="Search table of contents" onChange={this.handleFilterChange} />
          <button type="submit"><FontAwesomeIcon icon={['far', 'search']} className="dp-po-icon" /></button>
        </form>
        <div className="dp-po-guides-search-block">
          {this.renderList()}
        </div>
      </div>
    );
  }
}
export default PageList;
