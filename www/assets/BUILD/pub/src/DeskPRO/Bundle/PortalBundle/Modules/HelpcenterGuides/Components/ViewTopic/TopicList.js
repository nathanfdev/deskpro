import PropTypes from 'prop-types';
import React from 'react';
import classNames from 'classnames';
import Isvg from 'react-inlinesvg';
import guideDefault from '@deskpro/portal-style/dist/img/page-icons/guide-default.svg';
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
    const { expanded } = this.state;
    if (!recursive && typeof expanded[topic.id] !== 'undefined') {
      return expanded[topic.id];
    }
    return topic.slug === topicSlug || Object.values(topic.children).find(c => this.isExpandedTopic(c, true));
  };

  toggleTopic = (e, topic, initial) => {
    e.preventDefault();
    e.stopPropagation();
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
      filter: e.target.value
    });
  };

  locationHasChanged = (e) => {
    this.setState({
      path: e.pathname
    });
  };

  renderTopics(topics, depth = 0, collapse = false) {
    const { guideSlug, topicSlug, grabTopicFromApi } = this.props;
    const { filter, expanded } = this.state;
    return (
      <ul className={classNames('dp-po-guides-search-content-list', { collapse })}>
        {topics
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
              grabTopicFromApi={grabTopicFromApi}
              filter={filter}
              filterTopic={this.filterTopic}
              toggleTopic={this.toggleTopic}
              expanded={!!(filter !== '' || this.isExpandedTopic(topic))}
              expandedList={expanded}
            />
            )
          )}
      </ul>
    );
  }

  renderList() {
    const { guide, topics, twoLevelSection } = this.props;
    if (twoLevelSection) {
      return (
        <div className="dp-po-guides-search-content accordion" id="accordionExample">
          {topics
            .filter(t => t.depth === 0)
            .filter(t => this.filterTopic(t))
            .sort((a, b) => parseInt(a.display_order, 10) - parseInt(b.display_order, 10))
            .map((topic) => {
              const collapsed = !this.isExpandedTopic(topic);
              return (
                <div className="dp-po-guides-search-content-accordion" key={topic.slug}>
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
                    <span className="title">{topic.title}</span> <i className="dp-po-icon far fa-angle-down" />
                  </div>
                  {this.renderTopics(Object.values(topic.children), 1, collapsed)}
                </div>
              );
            }
          )}
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
          <input type="text" value={filter} placeholder="Search table of contents" onChange={this.handleFilterChange} />
          <button type="submit"><i className="dp-po-icon far fa-search" /></button>
        </form>
        <div className="dp-po-guides-search-block">
          {this.renderList()}
        </div>
      </div>
    );
  }
}
export default TopicList;
