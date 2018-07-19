import React from 'react';
import PropTypes from 'prop-types';
import { Icon, ListElement, ToggleableList } from '@deskpro/react-components';
import Articles from './Articles';
import Downloads from './Downloads';
import Feedback from './Feedback';
import News from './News';

const Drawer = ({ onClick, heading, opened, children }) => (
  <ListElement>
    <h3 onClick={onClick}>
      {heading}
    </h3>
    <div style={{ display: opened ? 'block' : 'none' }}>
      {children}
    </div>
  </ListElement>
);

Drawer.propTypes = {
  onClick:  PropTypes.func,
  heading:  PropTypes.node,
  opened:   PropTypes.bool,
  children: PropTypes.node,
};

export default class Publishing extends React.Component {
  static propTypes = {
    results: PropTypes.object
  };

  constructor(props) {
    super(props);
    this.state = {
      activeDrawer: 'feedback'
    };
  }

  onSelect = (selected, drawer) => {
    this.setState({
      activeDrawer: drawer
    });
  };

  render() {
    const { results } = this.props;
    const { activeDrawer } = this.state;
    const drawers = [];

    if (results.feedback && results.feedback.length) {
      drawers.push(
        <Drawer
          key="feedback"
          heading={<span><Icon name="thumbs-up" /> Feedback</span>}
          opened={activeDrawer === 'feedback'}
          onClick={selected => this.onSelect(selected, 'feedback')}
        >
          <Feedback feedback={results.feedback} />
        </Drawer>
      );
    }
    if (results.articles && results.articles.length) {
      drawers.push(
        <Drawer
          key="articles"
          heading={<span><Icon name="list-alt" /> Articles</span>}
          opened={activeDrawer === 'articles'}
          onClick={selected => this.onSelect(selected, 'articles')}
        >
          <Articles articles={results.articles} />
        </Drawer>
      );
    }
    if (results.news && results.news.length) {
      drawers.push(
        <Drawer
          key="news"
          heading={<span><Icon name="bolt" /> News</span>}
          opened={activeDrawer === 'news'}
          onClick={selected => this.onSelect(selected, 'news')}
        >
          <News news={results.news} />
        </Drawer>
      );
    }
    if (results.downloads && results.downloads.length) {
      drawers.push(
        <Drawer
          key="downloads"
          heading={<span><Icon name="download" /> Downloads</span>}
          opened={activeDrawer === 'downloads'}
          onClick={selected => this.onSelect(selected, 'downloads')}
        >
          <Downloads downloads={results.downloads} />
        </Drawer>
      );
    }
    if (drawers.length === 0) {
      return null;
    }
    return (
      <section>
        <ToggleableList on="click" toggle="opened" whenType={Drawer} controlled>
          {drawers}
        </ToggleableList>
      </section>
    );
  }
}
