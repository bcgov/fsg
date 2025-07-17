<template>
    <div v-if="demographics && demographics.length > 0" class="col-12">
        <hr />
        <h5 class="mb-3">Demographics Information</h5>
        
        <div v-for="demographic in demographics" :key="demographic.id" class="mb-3">
            <Label :for="'demographic_' + demographic.id" class="form-label">
                {{ demographic.question }}
                <span v-if="demographic.required" class="text-danger">*</span>
            </Label>
            
            <!-- Text Input -->
            <Input 
                v-if="demographic.type === 'text'"
                :id="'demographic_' + demographic.id"
                type="text" 
                class="form-control"
                :value="getDemographicAnswer(demographic.id)"
                @input="updateDemographicAnswer(demographic.id, $event.target.value)"
                :readonly="readonly"
                :disabled="readonly"
            />
            
            <!-- Select Dropdown -->
            <Select 
                v-else-if="demographic.type === 'select'"
                :id="'demographic_' + demographic.id"
                class="form-select"
                :value="getDemographicAnswer(demographic.id)"
                @change="updateDemographicAnswer(demographic.id, $event.target.value)"
                :readonly="readonly"
                :disabled="readonly"
            >
                <option value="">Please select...</option>
                <option 
                    v-for="option in demographic.options" 
                    :key="option.id"
                    :value="option.value || option.label"
                >
                    {{ option.label }}
                </option>
            </Select>
            
            <!-- Radio Buttons -->
            <div v-else-if="demographic.type === 'radio'" class="mt-1">
                <div v-for="option in demographic.options" :key="option.id" class="form-check">
                    <input 
                        :id="'demographic_' + demographic.id + '_' + option.id"
                        :name="'demographic_' + demographic.id"
                        type="radio" 
                        class="form-check-input"
                        :value="option.value || option.label"
                        :checked="getDemographicAnswer(demographic.id) === (option.value || option.label)"
                        @change="updateDemographicAnswer(demographic.id, $event.target.value)"
                        :readonly="readonly"
                        :disabled="readonly"
                    />
                    <label 
                        :for="'demographic_' + demographic.id + '_' + option.id" 
                        class="form-check-label"
                    >
                        {{ option.label }}
                    </label>
                </div>
            </div>
            
            <!-- Checkboxes -->
            <div v-else-if="demographic.type === 'checkbox'" class="mt-1">
                <div v-for="option in demographic.options" :key="option.id" class="form-check">
                    <input 
                        :id="'demographic_' + demographic.id + '_' + option.id"
                        type="checkbox" 
                        class="form-check-input"
                        :value="option.value || option.label"
                        :checked="isOptionSelected(demographic.id, option.value || option.label)"
                        @change="toggleCheckboxOption(demographic.id, option.value || option.label, $event.target.checked)"
                        :readonly="readonly"
                        :disabled="readonly"
                    />
                    <label 
                        :for="'demographic_' + demographic.id + '_' + option.id" 
                        class="form-check-label"
                    >
                        {{ option.label }}
                    </label>
                </div>
            </div>
            
            <!-- Multi-select -->
            <Select 
                v-else-if="demographic.type === 'multi-select'"
                :id="'demographic_' + demographic.id"
                class="form-select"
                multiple
                :value="getDemographicAnswerArray(demographic.id)"
                @change="updateMultiSelectAnswer(demographic.id, $event)"
                :readonly="readonly"
                :disabled="readonly"
            >
                <option 
                    v-for="option in demographic.options" 
                    :key="option.id"
                    :value="option.value || option.label"
                >
                    {{ option.label }}
                </option>
            </Select>
            
            <small v-if="demographic.description" class="form-text text-muted">
                {{ demographic.description }}
            </small>
        </div>
    </div>
</template>

<script>
import Input from '@/Components/Input.vue';
import Select from '@/Components/Select.vue';
import Label from '@/Components/Label.vue';

export default {
    name: 'StudentDemographics',
    components: {
        Input,
        Select,
        Label
    },
    props: {
        demographics: {
            type: Array,
            default: () => []
        },
        modelValue: {
            type: Object,
            default: () => ({})
        },
        existingDemographics: {
            type: Object,
            default: () => ({})
        },
        readonly: {
            type: Boolean,
            default: false
        }
    },
    emits: ['update:modelValue'],
    data() {
        return {
            demographicAnswers: { ...this.existingDemographics, ...this.modelValue }
        }
    },
    watch: {
        modelValue: {
            handler(newVal) {
                this.demographicAnswers = { ...newVal };
            },
            deep: true
        },
        demographicAnswers: {
            handler(newVal) {
                this.$emit('update:modelValue', newVal);
            },
            deep: true
        }
    },
    methods: {
        getDemographicAnswer(demographicId) {
            return this.demographicAnswers[demographicId] || '';
        },
        
        getDemographicAnswerArray(demographicId) {
            const answer = this.demographicAnswers[demographicId];
            if (Array.isArray(answer)) {
                return answer;
            }
            return answer ? answer.split(',') : [];
        },
        
        updateDemographicAnswer(demographicId, value) {
            if (this.readonly) return;
            this.demographicAnswers[demographicId] = value;
        },
        
        updateMultiSelectAnswer(demographicId, event) {
            if (this.readonly) return;
            const selectedOptions = Array.from(event.target.selectedOptions, option => option.value);
            this.demographicAnswers[demographicId] = selectedOptions.join(',');
        },
        
        isOptionSelected(demographicId, optionValue) {
            const answer = this.demographicAnswers[demographicId];
            if (!answer) return false;
            
            // Handle comma-separated values for checkboxes
            const selectedValues = answer.split(',').map(v => v.trim());
            return selectedValues.includes(optionValue);
        },
        
        toggleCheckboxOption(demographicId, optionValue, isChecked) {
            if (this.readonly) return;
            
            const currentAnswer = this.demographicAnswers[demographicId] || '';
            let selectedValues = currentAnswer ? currentAnswer.split(',').map(v => v.trim()) : [];
            
            if (isChecked) {
                if (!selectedValues.includes(optionValue)) {
                    selectedValues.push(optionValue);
                }
            } else {
                selectedValues = selectedValues.filter(v => v !== optionValue);
            }
            
            this.demographicAnswers[demographicId] = selectedValues.join(',');
        }
    }
}
</script>
