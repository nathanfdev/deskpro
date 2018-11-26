import React from 'react';
import PropTypes from 'prop-types';
import { Tabs, TabLink } from '@deskpro/react-components';

export default class TopTabs extends React.Component {
  static propTypes = {
    active:   PropTypes.string,
    results:  PropTypes.object,
    onChange: PropTypes.func,
  };

  getAdminCount = () => {
    const { results } = this.props;
    if (results.admin && results.admin.length) {
      return results.admin.length;
    }
    return 0;
  };

  getPublishingCount = () => {
    const { results } = this.props;
    const elements = ['feedback', 'articles', 'news', 'downloads'];
    let count = 0;
    elements.forEach((e) => {
      if (results[e] && results[e].length) {
        count += results[e].length;
      }
    });
    return count;
  };

  render() {
    const { onChange, active } = this.props;
    const tabs = [];
    if (this.getPublishingCount() > 0) {
      tabs.push(
        <TabLink name="publishing" key="publishing">
          Publishing <span className="count">{this.getPublishingCount()}</span>
        </TabLink>
      );
    }
    if (this.getAdminCount() > 0) {
      tabs.push(
        <TabLink name="admin" key="admin">
          Admin <span className="count">{this.getAdminCount()}</span>
        </TabLink>
      );
    }
    if (tabs.length === 0) {
      return null;
    }
    tabs.push(<TabLink key="empty" />);

    return (
      <Tabs active={active} onChange={onChange} className="top-tabs" allowUnselect>
        {tabs}
      </Tabs>
    );
  }
}
