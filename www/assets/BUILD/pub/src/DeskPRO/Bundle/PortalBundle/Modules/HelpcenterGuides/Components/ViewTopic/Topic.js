import React from 'react';
import PropTypes from 'prop-types';
import { injectIntl } from 'react-intl';
import moment from 'moment';
import { TopicSummary } from '../index';

class Topic extends React.PureComponent {
  static propTypes = {
    intl:  PropTypes.object,
    topic: PropTypes.object,
    data:  PropTypes.object,
  };

  static defaultProps = {
    data: {}
  };

  render() {
    const { data, topic, intl } = this.props;

    const fixed = false;
    const agentBarHeight = 0;

    return (
      <div className="dp-po-guides-block-article" id={`topic_${topic.slug}`}>
        <div className="row">
          <div className="col-sm-9">
            <div className="dp-po-guides-block-article-left">
              <div className="dp-po-guides-block-header">
                <div>
                  <h2 className="dp-po-guides-block-title dp-po-clipboard">{topic.title} <a
                    className="dp-po-clipboard-link" data-toggle="tooltip"
                    data-placement="top" title={intl.formatMessage({ id: 'helpcenter.general.copy-to-clipboard' })}
                  ><i
                    className="dp-po-icon far fa-anchor"
                  /></a></h2>
                  { topic.parent &&
                  <a href="" className="dp-po-guides-block-chapter"><i
                    className="dp-po-icon fal fa-angle-right"
                  />
                    {topic.parent.title}</a>
                  }
                </div>
                <div className="dp-po-guides-block-extra">
                  <ul className="dp-po-guides-block-extra-list">
                    <li className="dp-po-guides-block-extra-item">
                      <a href="" className="dp-po-guides-block-extra-link"><i
                        className="dp-po-icon fal fa-print"
                      /></a>
                    </li>
                    <li className="dp-po-guides-block-extra-item">
                      <a href="" className="dp-po-guides-block-extra-link">
                        <i className="dp-po-icon fal fa-file-pdf" />
                      </a>
                    </li>
                  </ul>
                </div>
              </div>
              <div
                className="dp-po-post-content dp-po-guides-block-content"
                dangerouslySetInnerHTML={{ __html: data.content }}
              />
            </div>
          </div>
          <div className="col-sm-3">
            <div className="dp-po-guides-block-article-right">
              <TopicSummary content={data.content} fixed={fixed} agentBarHeight={agentBarHeight} />
              <div className="dp-po-guides-meta">
                <p>Published: <strong>{moment(data.date_published).format('DD/MM/YYYY')}</strong></p>
                <p>Last updated: <strong>{moment(data.date_updated).format('DD/MM/YYYY')}</strong></p>
              </div>
            </div>
          </div>
        </div>
      </div>
    );
  }
}

export default injectIntl(Topic);
