import React from "react";
import _ from "lodash";
import $ from "jquery";

export default function create(stores) {
  let DpLevelSelect = React.createClass({
    getInitialState: function() {
      let state = {
        value: this.props.initialValue || null
      };

      return state;
    },

    initOptions: function() {
      let flat = [];

      this.groupedOptions = this.groupOptions(this.props.options, null, [], flat)
      this.flatOptions = flat;
    },

    groupOptions: function(options, parent = null, oldParents = [], flat = []) {
      let group = [];

      options.forEach((opt) => {
        if ((parent === null && (!opt.parent || opt.parent === 0 || opt.parent === "0")) || (parent == opt.parent)) {
          opt = _.clone(opt);
          opt.parentsPath = _.clone(oldParents);

          oldParents.push(opt.id);
          opt.children = this.groupOptions(options, opt.id, oldParents, flat);
          oldParents.pop();

          group.push(opt);
          flat.push(opt);
        }
      });

      return group;
    },

    handleSelectChange: function(event) {
      let sel = $(event.target);
      let opt = sel.find('option:selected');
      let value = null;

      // Set value to new option
      if (opt.data('id')) {
        value = opt.data('id');

      } else {
        // Set value to the last option selected
        if (sel.data('parent') && sel.data('parent') !== 0 && sel.data('parent') !== "0") {
          value = sel.data('parent');
        }
      }

      this.setState({ value: value });
    },

    renderGroup: function(group, path, depth = 0, parentId = 0) {
      if (!group) return null;

      let subGroup = _.find(group, i => path.indexOf(i.id) !== -1);
      return (
        <div className="level">
          <div className="select">
            <select data-parent={parentId} defaultValue={subGroup? subGroup.value : null} onChange={this.handleSelectChange}>
              <option value="0"></option>
              {group.map(function(o) {
                return <option key={o.id} data-id={o.id} value={o.value}>{o.title}</option>;
              })}
            </select>
          </div>
          {subGroup && subGroup.children.length ? this.renderGroup(subGroup.children, path, depth+1, subGroup.id) : null}
        </div>
      );
    },

    render: function () {
      if (!this.groupedOptions) {
        this.initOptions();
      }

      let selected = [];

      if (this.state.value) {
        let found = _.find(this.flatOptions, i => i.id == this.state.value);
        if (found) {
          selected = _.clone(found.parentsPath);
          selected.push(this.state.value);
        }
      }

      return (
        <div className="dp-level-select">
          {this.renderGroup(this.groupedOptions, selected)}
        </div>
      );
    }
  });

  return {
    DpLevelSelect: DpLevelSelect
  };
}