import PropTypes from 'prop-types';
import React from 'react';
import { FormattedMessage } from 'react-intl';
import classNames from 'classnames';
import Isvg from 'react-inlinesvg';
import Highlighter from 'react-highlight-words';
import guideDefault from '@deskpro/portal-style/dist/img/page-icons/guide-default.svg';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { IconRenderer } from 'DeskPRO/Component/IconRenderer';
import TopicListItem from './TopicListItem';

class TopicList extends React.Component {
  static propTypes = {
    topics:           PropTypes.array,
    guideSlug:        PropTypes.string,
    guide:            PropTypes.object,
    topicSlug:        PropTypes.string,
    grabTopicFromApi: PropTypes.func,
    sizes:            PropTypes.object,
    twoLevelSection:  PropTypes.bool,
  };

  static contextTypes = {
    router: PropTypes.object.isRequired
  };

  static renderNoResults() {
    return (
      <div className="dp-po-guides-search-no-results">
        <FontAwesomeIcon icon={['far', 'search']} className="dp-po-icon" />
        <span><FormattedMessage id="helpcenter.guides.no_matching_topics" /></span>
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

  filterTopic = (topic) => {
    let { filter } = this.state;

    filter = filter.toLowerCase();
    return filter === '' || topic.title.toLowerCase().match(filter) || Object.values(topic.children).find(c => this.filterTopic(c));
  };

  isExpandedTopic = (topic, recursive = false) => {
    const { topicSlug } = this.props;
    const { expanded, filter } = this.state;
    if (filter) {
      return Object.values(topic.children).find(c => this.filterTopic(c));
    }
    if (topic.slug === topicSlug || Object.values(topic.children).find(c => this.isExpandedTopic(c, true))) {
      return true;
    }
    if (!recursive && typeof expanded[topic.id] !== 'undefined') {
      return expanded[topic.id];
    }
    if (recursive && typeof expanded[topic.id] !== 'undefined' && expanded[topic.id]) {
      return true;
    }
    return false;
  };

  grabTopicFromApi = (slug) => {
    this.setState({
      filter: ''
    });
    this.props.grabTopicFromApi(slug);
  }

  toggleTopic = (e, topic, initial) => {
    e.preventDefault();
    e.stopPropagation();
    this.setState({
      filter: ''
    });
    const { expanded } = this.state;
    if (typeof expanded[topic.id] !== 'undefined') {
      expanded[topic.id] = !expanded[topic.id];
    } else {
      expanded[topic.id] = !initial;
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

  renderTopics(topics, depth = 0, collapse = false) {
    const { guideSlug, topicSlug } = this.props;
    const { filter, expanded } = this.state;
    const renderedTopics = topics
      .filter(t => depth > 0 || t.depth === depth)
      .filter(t => this.filterTopic(t))
      .sort((a, b) => parseInt(a.display_order, 10) - parseInt(b.display_order, 10))
      .map(topic => (
        <TopicListItem
          key={topic.slug}
          topic={topic}
          guideSlug={guideSlug}
          topicSlug={topicSlug}
          path={this.state.path}
          grabTopicFromApi={this.grabTopicFromApi}
          filter={filter}
          filterTopic={this.filterTopic}
          toggleTopic={this.toggleTopic}
          expanded={!!(this.isExpandedTopic(topic))}
          expandedList={expanded}
        />
        )
      );
    if (renderedTopics.length === 0) {
      return TopicList.renderNoResults();
    }
    return (
      <ul className={classNames('dp-po-guides-search-content-list', { collapse })}>
        {renderedTopics}
      </ul>
    );
  }

  renderList() {
    const { guide, topics, twoLevelSection } = this.props;
    const { filter } = this.state;
    if (twoLevelSection) {
      const renderedTopics = topics
        .filter(t => t.depth === 0)
        .filter(t => this.filterTopic(t))
        .sort((a, b) => parseInt(a.display_order, 10) - parseInt(b.display_order, 10))
        .map((topic) => {
          const collapsed = !this.isExpandedTopic(topic);
          return (
            <div className={classNames('dp-po-guides-search-content-accordion', { collapsed })} key={topic.slug}>
              <div
                className={classNames('dp-po-guides-search-content-title', { collapsed })}
                onClick={e => this.toggleTopic(e, topic, this.isExpandedTopic(topic))}
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
                  textToHighlight={topic.title}
                />
                <FontAwesomeIcon icon={['far', 'angle-down']} className="dp-po-icon" />
              </div>
              {this.renderTopics(Object.values(topic.children), 1, collapsed)}
            </div>
          );
        }
        );
      if (renderedTopics.length === 0) {
        return TopicList.renderNoResults();
      }
      return (
        <div className="dp-po-guides-search-content accordion" id="accordionExample">
          {renderedTopics}
        </div>
      );
    }
    return this.renderTopics(topics);
  }

  render() {
    const { sizes } = this.props;
    const { filter } = this.state;
    const style = {};
    if (sizes) {
      style.width = sizes.searchWidth;
    }
    return (
      <div className="dp-po-guides-search" style={style}>
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
export default TopicList;
