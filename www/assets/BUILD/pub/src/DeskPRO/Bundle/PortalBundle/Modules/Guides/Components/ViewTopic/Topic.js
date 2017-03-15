import React, { PropTypes } from 'react';
import Link from 'react-router/lib/Link';
import classNames from 'classnames';

class Topic extends React.Component {
  static propTypes = {
    topic:      PropTypes.object,
    locale:     PropTypes.string,
    guideSlug:  PropTypes.string,
    expandable: PropTypes.bool
  };

  static defaultProps = {
    expandable: true,
  };

  constructor(props) {
    super(props);
    let expanded = !this.props.expandable;
    if (this.props.expandable && window.location.href.match(props.topic.slug)) {
      expanded = true;
    }
    this.state = {
      expanded
    };
  }

  getChildren = () => {
    const { topic, locale, guideSlug, expandable } = this.props;
    if (!Object.values(topic.children).length) {
      return null;
    }
    return (
      <ul
        className={classNames({
          hidden: expandable && !this.state.expanded,
          expandable
        })}
      >
        {Object.values(topic.children)
          .sort((a, b) => parseInt(a.display_order, 10) - parseInt(b.display_order, 10))
          .map(child => (
            <Topic key={child.slug} topic={child} locale={locale} guideSlug={guideSlug} />
          )
        )}
      </ul>
    );
  };

  toggleChildren = () => {
    this.setState({
      expanded: !this.state.expanded
    });
  };

  render() {
    const { topic, locale, guideSlug } = this.props;
    let { expandable } = this.props;
    if (!Object.values(topic.children).length) {
      expandable = false;
    }
    return (
      <li className="topic-item" key={topic.slug}>
        <Link to={`/${locale}/guides/${guideSlug}${topic.parents_slug}/${topic.slug}`} activeClassName="active" onClick={this.toggleChildren}>
          {topic.title}
          {expandable ? <i
            className={classNames(
            'fa pull-right',
            { 'fa-caret-right': !this.state.expanded, 'fa-caret-down': this.state.expanded })}
          /> : null }
        </Link>
        {this.getChildren()}
      </li>
    );
  }
}
export default Topic;
