import PropTypes from 'prop-types';
import React from 'react';
import Link from 'react-router/lib/Link';
import Highlighter from 'react-highlight-words';
import classNames from 'classnames';

class TopicListItem extends React.Component {
  static propTypes = {
    topic:            PropTypes.object,
    guideSlug:        PropTypes.string,
    topicSlug:        PropTypes.string,
    path:             PropTypes.string,
    filter:           PropTypes.string,
    expanded:         PropTypes.bool,
    expandedList:     PropTypes.object,
    grabTopicFromApi: PropTypes.func,
    filterTopic:      PropTypes.func,
    toggleTopic:      PropTypes.func,
  };

  static defaultProps = {
    expandable: true,
  };

  getLevelPrefix = (delta = 0) => {
    const { topic } = this.props;
    switch (topic.depth + delta) {
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
    const { topic, expanded } = this.props;
    if (['path', 'svg', 'FIGURE'].indexOf(e.target.tagName) !== -1) {
      this.props.toggleTopic(e, topic, expanded);
    } else {
      this.props.grabTopicFromApi(topic.slug);
    }
  };

  isExpanded = () => {
    const { expandedList, topic, expanded } = this.props;
    if (typeof expandedList[topic.id] !== 'undefined') {
      return expandedList[topic.id];
    }
    return expanded;
  }

  renderChildren = () => {
    const { topic, guideSlug, topicSlug, expandedList, filter, filterTopic, toggleTopic, grabTopicFromApi } = this.props;
    if (!Object.values(topic.children).length) {
      return null;
    }
    const prefix = this.getLevelPrefix(1);
    const style = {};
    if (!this.isExpanded()) {
      style.display = 'none';
    }
    return (
      <ul
        className={classNames(`dp-po-guides-search-content-${prefix}list`)}
        style={style}
      >
        {Object.values(topic.children)
          .filter(t => filterTopic(t))
          .sort((a, b) => parseInt(a.display_order, 10) - parseInt(b.display_order, 10))
          .map(child => (
            <TopicListItem
              key={child.slug}
              topic={child}
              guideSlug={guideSlug}
              topicSlug={topicSlug}
              path={this.props.path}
              grabTopicFromApi={grabTopicFromApi}
              filter={filter}
              filterTopic={filterTopic}
              toggleTopic={toggleTopic}
              expanded={(filter !== '' || child.slug === topicSlug || typeof Object.values(child.children)
                .find(c => c.slug === topicSlug ||  Object.values(c.children).find(cc => cc.slug === topicSlug)) !== 'undefined')}
              expandedList={expandedList}
            />
            )
          )}
      </ul>
    );
  };

  render() {
    const { topic, topicSlug, guideSlug, toggleTopic, expanded, filter } = this.props;

    let baseUrl = window.DESKPRO_BASE_URL;
    if (baseUrl) {
      baseUrl = baseUrl.replace(/\/+$/, '');
    }

    const prefix = this.getLevelPrefix();
    if (topic.no_content === '1') {
      return (
        <li className={`dp-po-guides-search-content-${prefix}item`} key={topic.slug}>
          <div
            className={classNames(`dp-po-guides-search-content-${prefix}link chapter`, { expanded: this.isExpanded() })}
            onClick={e => toggleTopic(e, topic, expanded)}
          >
            <Highlighter
              highlightClassName="filter-highlight"
              className="dp-po-guide-topic-list-item"
              searchWords={[filter]}
              textToHighlight={topic.title}
            />
          </div>
          {this.renderChildren()}
        </li>
      );
    }
    return (
      <li className={`dp-po-guides-search-content-${prefix}item`} key={topic.slug}>
        <Link
          className={classNames(`dp-po-guides-search-content-${prefix}link`, { expanded: this.isExpanded(), active: topic.slug === topicSlug })}
          to={`${baseUrl}/guides/${guideSlug}${topic.parents_slug}/${topic.slug}`}
          activeClassName="active"
          onClick={this.handleClick}
        >
          <Highlighter
            highlightClassName="filter-highlight"
            className="dp-po-guide-topic-list-item"
            searchWords={[filter]}
            textToHighlight={topic.title}
          />
          {Object.values(topic.children).length > 0 && <figure className="dp-po-icon"><i className="fas fa-caret-down" /></figure>}
        </Link>
        {this.renderChildren()}
      </li>
    );
  }
}
export default TopicListItem;
