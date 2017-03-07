import React, { PropTypes } from 'react';

class TopicList extends React.Component {
  static propTypes = {
    topics: PropTypes.array
  };

  getChildren = (topic) => {
    if (!topic.children.length) {
      return null;
    }
    return (
      <ul>
        {topic.children.map(child => (
          <li className="topic-item" key={child.slug}>
            <a onClick={() => { this.openTopic(child); }}>{child.title}</a>
            {this.getChildren(child)}
          </li>
        ))}
      </ul>
    );
  };

  openTopic = (topic) => {
    console.log(topic);
  };

  render() {
    return (<ul>
      {this.props.topics.map(topic => (
        <li className="topic-item" key={topic.slug}>
          <a onClick={() => { this.openTopic(topic); }}>{topic.title}</a>
          {this.getChildren(topic)}
        </li>
      ))}
    </ul>);
  }
}
export default TopicList;
