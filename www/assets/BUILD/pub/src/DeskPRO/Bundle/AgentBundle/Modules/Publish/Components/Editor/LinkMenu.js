import PropTypes from 'prop-types';
import React from 'react';
import Select from 'react-select-plus';
import { connect } from 'react-redux';
import invariant from 'invariant';
import { quickSearchAction } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Actions/search';
import { Input } from 'DeskPRO/Component/Semantic/Form';
import { Button } from 'DeskPRO/Component/Semantic/Button';

@connect()
class LinkMenu extends React.Component {
  static propTypes = {
    insertLink: PropTypes.func,
    dispatch:   PropTypes.func.isRequired
  };

  static onFocus() {
    window.DeskPRO_Window.keyboardShortcuts.isPaused = true;
  }

  static onBlur() {
    window.DeskPRO_Window.keyboardShortcuts.isPaused = false;
  }

  constructor(props) {
    super(props);
    this.state = {
      title:   '',
      url:     '',
      content: '',
    };
  }

  onChange = (content) => {
    this.setState({
      content
    });
  };

  getOptions = (input, callback) => {
    if (!input) {
      callback(null, []);
    }
    const types = ['article', 'download', 'news', 'feedback', 'topic'];
    this.props.dispatch(quickSearchAction({ types, query: input })).then((res) => {
      invariant(res.data && res.data.data && res.data.data.grouped_results, 'Malformed QuickSearch response');

      const options = [];

      for (const group of res.data.data.grouped_results) {
        const option = { label: '', options: [] };
        options.push(option);

        if (group.type === 'article') {
          option.label = 'Articles';
        } else if (group.type === 'download') {
          option.label = 'Downloads';
        } else if (group.type === 'feedback') {
          option.label = 'Feedback';
        } else if (group.type === 'news') {
          option.label = 'News';
        } else if (group.type === 'topic') {
          option.label = 'Topics';
        }

        for (const result of group.results) {
          option.options.push({
            label: result.title,
            value: result.id,
            type:  group.type,
            data:  result
          });
        }
      }

      callback(null, { options });
    });
  };

  setUrl = (event) => {
    this.setState({
      url: event.target.value
    });
  };

  setTitle = (event) => {
    this.setState({
      title: event.target.value
    });
  };

  insert = () => {
    const { title, url, content } = this.state;
    let link = '';
    if (content) {
      if (title) {
        link = `[${title}]{{ content(${content.type},${content.value}) }}`;
      } else {
        link = `{{ content_link(${content.type},${content.value}) }}`;
      }
    } else if (title) {
      link = `[${title}](${url})`;
    } else {
      link = `<${url}>`;
    }
    this.props.insertLink(link);
  };

  render() {
    return (
      <div>
        <div className="header">Add Link</div>
        <div className="description">
          <label htmlFor="content_link_title">Title: </label><br />
          <Input type="text" id="content_link_title" value={this.state.title} onChange={this.setTitle} /><br />
          <label htmlFor="content_link_title">Url: </label><br />
          <Input type="text" id="content_link_title" value={this.state.url} onChange={this.setUrl} /><br />
          <br />
          OR
          <br />
          <br />
          <label htmlFor="content_link_select">Content link :</label><br />
          <Select.Async
            id="content_link_select"
            minimumInput={3}
            onChange={this.onChange}
            onFocus={LinkMenu.onFocus}
            onBlur={LinkMenu.onBlur}
            loadOptions={this.getOptions}
            placeholder="Select a content"
            value={this.state.content}
          /><br />
          <Button onClick={this.insert} disabled={!!(this.state.content && this.state.url)}>Insert</Button>
        </div>
      </div>
    );
  }
}
export default LinkMenu;
